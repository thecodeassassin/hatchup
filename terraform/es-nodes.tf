resource "aws_security_group" "es_elb" {
  name        = "hatchup_es_loadbalancer_sg"
  description = "Hatchup ES loadbalancer security group"
  vpc_id      = "${aws_vpc.default.id}"

  # Elasticsearch access from the VPC
  ingress {
    from_port   = 9200
    to_port     = 9200
    protocol    = "tcp"
    cidr_blocks = ["10.0.0.0/16"]
  }

  # outbound internet access
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

resource "aws_elb" "es" {
  name = "hatchup-es-elb"

  subnets         = ["${aws_subnet.default.id}"]
  security_groups = ["${aws_security_group.es_elb.id}"]
  instances       = ["${aws_instance.es.*.id}"]

  # this loadbalancer is internal only
  internal = true

  listener {
    instance_port     = 9200
    instance_protocol = "http"
    lb_port           = 9200
    lb_protocol       = "http"
  }

}


resource "aws_instance" "es" {
  connection {
    user = "ubuntu"
  }

  # name the hosts so we can manage them with ansible
  tags {
    Name = "${format("es-%03d", count.index + 1)}"
    Group = "es"
  }

  # instance type is t2.micro by default
  instance_type = "${var.instance_type}"

  ami = "${lookup(var.aws_amis, var.aws_region)}"
  key_name = "${var.key_name}"
  vpc_security_group_ids = ["${aws_security_group.default.id}"]
  subnet_id = "${aws_subnet.default.id}"

  # configure this to scale the es instances
  count = 2
}

# A security group for the ELB so it is accessible via the web
resource "aws_security_group" "web_elb" {
  name        = "hatchup_web_loadbalancer_sg"
  description = "Hatchup web loadbalancer security group"
  vpc_id      = "${aws_vpc.default.id}"

  # HTTP access from anywhere
  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  # outbound internet access
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

resource "aws_elb" "web" {
  name = "hatchup-elb"

  subnets         = ["${aws_subnet.default.id}"]
  security_groups = ["${aws_security_group.web_elb.id}"]
  instances       = ["${aws_instance.web.*.id}"]

  listener {
    instance_port     = 80
    instance_protocol = "http"
    lb_port           = 80
    lb_protocol       = "http"
  }

}

resource "aws_instance" "web" {
  connection {
    user = "ubuntu"
  }

  # name the hosts so we can manage them with ansible
  tags {
    Name = "${format("web-%03d", count.index + 1)}"
    Group = "web"
  }

  # instance type is t2.micro by default
  instance_type = "${var.instance_type}"

  ami = "${lookup(var.aws_amis, var.aws_region)}"
  key_name = "${var.key_name}"
  vpc_security_group_ids = ["${aws_security_group.default.id}"]
  subnet_id = "${aws_subnet.default.id}"

  # configure this to scale the instances
  count = 2
}

resource "aws_elb" "web" {
  name = "hatchup-elb"

  subnets         = ["${aws_subnet.default.id}"]
  security_groups = ["${aws_security_group.elb.id}"]
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

  instance_type = "t2.micro"
  ami = "${lookup(var.aws_amis, var.aws_region)}"
  key_name = "${var.key_name}"
  vpc_security_group_ids = ["${aws_security_group.default.id}"]
  subnet_id = "${aws_subnet.default.id}"

  # configure this to scale the instances
  count = 2
}
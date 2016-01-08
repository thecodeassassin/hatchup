output "web_address" {
  value = "${aws_elb.web.dns_name}"
}

output "es_address" {
  value = ""
}

output "mysql_address" {
  value = "${aws_db_instance.default.address}"
}


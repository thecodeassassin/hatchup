output "web_address" {
  value = "${aws_elb.web.dns_name}"
}

output "es_address" {
  value = "${aws_elb.es.dns_name}"
}

output "mysql_address" {
  value = "${aws_db_instance.default.endpoint}"
}
output "mysql_port" {
  value = "${aws_db_instance.default.port}"
}

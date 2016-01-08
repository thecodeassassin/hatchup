resource "aws_db_subnet_group" "default" {
  name = "main"
  description = "Db subnet"
  subnet_ids = ["${aws_subnet.default.id}", "${aws_subnet.backup.id}"]
  tags {
    Name = "Hatchup subnet group"
  }
}

resource "aws_db_instance" "default" {
  identifier = "hatchup-rds"
  allocated_storage = 5
  engine = "mariadb"
  engine_version = "10.0.17"

  # todo make this configurable
  instance_class = "db.t2.micro"

  name = "hatchup"

  # in a full fledged application this would obviously be true
  multi_az = false

  username = "root"
  password = "${var.mysql_root_password}"
  db_subnet_group_name = "${aws_db_subnet_group.default.name}"

  # the db should not have a public IP
  publicly_accessible = false
}
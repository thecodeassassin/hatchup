# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  config.vm.hostname = 'test.tca0.nl'
  config.vm.box = "ubuntu/trusty64"

  config.vm.network :private_network, ip: "192.168.33.39"

  config.vm.define "web-001"

  config.vm.provider :virtualbox do |v|
    v.name = "hatch-test"
    v.memory = 512
    v.cpus = 1
    v.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    v.customize ["modifyvm", :id, "--ioapic", "on"]
  end

  config.vm.synced_folder "./application", "/var/www", id: "unique-id", type: nil,
        group: 'www-data', owner: 'www-data', mount_options: ["dmode=775", "fmode=764"]

  # Enable provisioning with Ansible.
  config.vm.provision "ansible" do |ansible|
    #ansible.verbose = "vvv"

    ansible.host_vars = {
        "web-001" => {"php_error_reporting" => "E_ALL",
                    "php_display_errors" => "On"}
      }

    ansible.groups = {
      "tag_Group_web" => ["web-001"],

      # set development specific values. We need nginx_sendfile to be off otherwise virtualbox will truncate responses
      "tag_Group_web:vars" => {"php_display_errors" => "On",
                               "php_display_startup_errors" => "On"
                               "nginx_sendfile" => "off"
                              },
      "tag_Group_es" => ["web-001"]
    }
    ansible.playbook = "ansible/playbook.yml"
  end

end

# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  config.vm.hostname = 'test.tca0.nl'
  config.vm.box = "ubuntu/trusty64"

  config.vm.network :private_network, ip: "192.168.33.39"
  config.vm.network "forwarded_port", guest: 2375, host: 2375

  config.vm.provider :virtualbox do |v|
    v.name = "test-server-hatchup"
    v.memory = 256
    v.cpus = 1
    v.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    v.customize ["modifyvm", :id, "--ioapic", "on"]
  end

  # Enable provisioning with Ansible.
  config.vm.provision "ansible" do |ansible|
    #ansible.verbose = "vvv"
    ansible.groups = {
      "Tag_web" => ["web-001"],
      "all_groups:children" => ["Tag_web"]
    }
    ansible.playbook = "ansible/playbook.yml"
  end

end


This repository contains independent Shopware extensions designed to connect a Shopware e-commerce store to self-hosted Python microservices.

### **Prerequisite**
These extensions require the core microservices hosted in the companion repository: 
[Automation Utilities](https://github.com/nawar16/AutomationUtilities)


### **Installation**
To install a specific extension, configure a Composer path repository in the Shopware installation, or copy the desired extension folder directly into the Shopware project directory:
```bash
cp -r ./[ExtensionName] /path-to-shopware/custom/plugins/[ExtensionName]
cd /path-to-shopware
bin/console plugin:refresh
bin/console plugin:install --activate [ExtensionName]
```
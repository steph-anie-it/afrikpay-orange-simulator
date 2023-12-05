pipeline {
    agent any
    environment {
        ENV_FILES = " "
    }
    stages {
        stage ('deploy') {
            steps {
                sshagent(credentials : ['ssh-new-prod-server-ssh']) {
                    sh '''
                        ssh -o StrictHostKeyChecking=no -t $USERNAME@$SSH_PROD_HOST  "
                        cd /var/www/AfrikPaySimulator && sudo rm -R afrikpay-com-orange-simulator-cr || true &&
                        git clone -b qa https://${GIT_USERNAME}:${GIT_TOKEN}@github.com/afrikpay/afrikpay-com-orange-simulator-cr.git &&
                        cd afrikpay-com-orange-simulator-cr && rm *.lock && sudo mkdir var/ var/log var/cache var/cache/prod var/cache/dev &&
                        sudo chmod -R 777 var/ var/log/  var/cache var/cache/prod var/cache/dev  && composer install && sudo chmod -R 777 var/ && sudo rm -rf var/cache/prod var/cache/dev && php bin/console d:s:u -f --complete
                        "
                    '''
                }
            }
          post{
            always{
               deleteDir()
               emailext to: "$RECIPIENTS",
               subject: "${env.JOB_NAME}:${currentBuild.currentResult}",
               body: "${currentBuild.currentResult}: Job ${env.JOB_NAME} is ${currentBuild.currentResult}."
            }
         }
        }
    }
}

pipeline {
    agent any

    environment {
        MAIL_RECIPIENTS = "stephanietakam@it.afrikpay.com"  // 📧 Adresse email pour recevoir le baseline PHPStan
    }

    stages {
        stage('Checkout') {
            steps {
                git branch: 'main', url: 'https://github.com/steph-anie-it/afrikpay-orange-simulator.git'
            }
        }

        stage('Build application') {
            steps {
                sh 'composer install  --no-interaction --prefer-dist'
            }
        }

        stage('Analyse') {
            steps{
                sh 'vendor/bin/phpstan analyse src --configuration=phpstan.dist.neon || true'
                sh 'vendor/bin/phpstan analyse src --generate-baseline'
            }
        }

        stage('Test') {
            steps{
                sh 'vendor/bin/phpunit --filter GenerateNumberTest'
            }
        }

        stage('Deploy') {
            when {
                expression { currentBuild.result == null || currentBuild.result == 'SUCCESS' }
            }
            steps {
                sh 'symfony server:start --daemon' // 🔄 Remplace par ta commande de déploiement
            }
        }

    }

    post {
        always {
            script {
                if (fileExists('phpstan-baseline.neon')) {
                    emailext (
                        to: "${MAIL_RECIPIENTS}",
                        subject: "PHPStan Baseline - ${env.JOB_NAME} #${env.BUILD_NUMBER}",
                        body: "Bonjour,\n\nVoici le fichier PHPStan Baseline du dernier build Jenkins.\n\nCordialement,\nJenkins",
                        attachmentsPattern: 'phpstan-baseline.neon'
                    )
                }
            }
        }
    }
}



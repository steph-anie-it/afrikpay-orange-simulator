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

        stage('Installation des dependances') {
            steps {
                sh 'composer install  --no-interaction --prefer-dist'
            }
        }

        stage('Analyse du code') {
            steps{
                sh '''
                if [ ! -f vendor/bin/phpstan ]; then
                    composer require --dev phpstan/phpstan
                else
                    vendor/bin/phpstan analyse --memory-limit=1G --generate-baseline
                fi
                '''
            }
        }

        stage('Lancement des Tests') {
            steps{
                sh '''
                if [ ! -f vendor/bin/phpunit ]; then
                    error "Aucun test trouvé ! Échec du déploiement."
                else
                    vendor/bin/phpunit --coverage-html coverage-report --coverage-text
                fi
                '''
                archiveArtifacts artifacts: 'coverage-report/**', fingerprint: true
            }
        }

    }

    post {
        always {
            sh '''
            start $WORKSPACE\coverage-report\index.html
            start $WORKSPACE\phpstan-baseline.neon
            '''
        }
    }
}
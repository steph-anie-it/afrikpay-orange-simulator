pipeline {
    agent any

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
                    vendor/bin/phpunit
                fi
                '''
            }
        }

        stage('Deploiement') {
            when {
                expression {  currentBuild.result == null || currentBuild.result == 'SUCCESS' }
            }
            steps {
                sh 'docker compose down'
                sh 'docker compose build'
                sh 'docker compose up -d'
            }
        }
    }
}



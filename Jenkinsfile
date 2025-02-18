pipeline {
    agent any

    options {
        dockerfile true
    }

    stages {
        stage('Checkout') {
            steps {
                git branch: 'main', url: 'https://github.com/steph-anie-it/afrikpay-orange-simulator.git'
            }
        }

        stage('Build Docker') {
            steps {
                echo 'Construction des conteneurs Docker...'
                sh 'docker-compose build'
            }
        }

        stage('Démarrage des Conteneurs') {
            steps {
                echo 'Démarrage des services Docker...'
                sh 'docker-compose up -d'
            }
        }

        stage('Migrations & Cache') {
            steps {
                echo 'Exécution des migrations et clear cache...'
                sh 'docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction'
                sh 'docker-compose exec php php bin/console cache:clear'
            }
        }
    }

    post {
        success {
            echo 'Déploiement réussi !'
        }
        failure {
            echo 'Échec du pipeline !'
        }
    }
}



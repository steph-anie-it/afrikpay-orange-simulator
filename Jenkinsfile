pipeline {
    agent any

    stages {
        stage('Checkout') {
            steps {
                git branch: 'main', url: 'https://github.com/steph-anie-it/afrikpay-orange-simulator.git'
            }
        }

        stage('Build application') {
            steps {
                sh 'composer install'
                sh 'symfony server:start -d'
            }
        }
    }
}



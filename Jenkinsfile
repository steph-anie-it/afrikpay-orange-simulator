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
            steps {
                script {
                    def testExists = sh(script: "[ -f vendor/bin/phpunit ] && echo 'exists'", returnStdout: true).trim()
                    if (testExists != 'exists') {
                        error "❌ Aucun test trouvé ! Échec du déploiement."
                    } else {
                        def testResult = sh(script: "vendor/bin/phpunit --coverage-html coverage-report --coverage-text --log-junit test-results.xml", returnStatus: true)
                        if (testResult != 0) {
                            error "❌ Tests échoués ! Arrêt du pipeline."
                        }
                    }
                }
                junit 'test-results.xml'  // Intégration avec Test Results Analyzer
                archiveArtifacts artifacts: 'coverage-report/**', fingerprint: true
            }
        }

        stage('Send mail') {
            steps{
                emailext(
                    to: "stephaniesanders044@gmail.com",
                    subject: "${env.JOB_NAME}",
                    body: "Ceci est un test personnaliser \nVous pouvez consultez les logs depuis cette adresse: https://f3f5-154-72-169-33.ngrok-free.app/afrikpay-orange-simulator/ \nCredentials: \n Username: Steph-Anie \n Password: jscompany",
                    from: 'stephanietakam1@gmail.com',
                    mimeType: 'text/plain'
                )
            }
        }

    }

    post {
        success {
            sh "git rev-parse HEAD > last_successful_commit.txt"
        }
        failure {
            script {
                def lastCommit = sh(script: "cat last_successful_commit.txt", returnStdout: true).trim()
                if (lastCommit) {
                    echo "⚠️ Build failed! Rolling back to last successful commit: ${lastCommit}"
                    sh "git reset --hard ${lastCommit}"
                    sh "git clean -fd"  // Supprime les fichiers non suivis pour éviter les conflits
                    sh "git checkout ${lastCommit}"
                    echo "✅ Rebuild starting..."
                    build job: env.JOB_NAME, wait: false  // Relance le pipeline
                } else {
                    error "Aucun commit stable trouvé !"
                }
            }
        }
    }
}
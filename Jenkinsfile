pipeline {
    agent any

    environment {
        // Configuration des destinataires des e-mails
        EMAIL_RECIPIENTS = 'stephanietakam@it.afrikpay.com, stephaniesanders044@gmail.com'
        // Seuil de couverture de code (ex : 80 %)
        COVERAGE_THRESHOLD = 50
    }

    stages {
        // Étape 1 : Checkout du code
        // stage('Checkout') { 
        //     steps {
        //         checkout scmGit(branches: [[name: 'main']],
        //         userRemoteConfigs: [
        //             [ url: 'https://github.com/steph-anie-it/afrikpay-orange-simulator.git' ]
        //         ])
        //     }
        // }

        // Étape 2 : Installation des dépendances
        stage('Installation des dependances') {
            steps {
                sh 'composer install --no-interaction --prefer-dist'
            }
        }

        // Étape 3 : Analyse de code avec PHPStan
        stage('Analyse du code') {
            steps{
                sh '''
                if [ ! -f vendor/bin/phpstan ]; then
                    composer require --dev phpstan/phpstan
                fi
                '''
                sh 'vendor/bin/phpstan analyse --memory-limit=1G --generate-baseline'
            }
        }

        // Étape 4 : Exécution des tests
        stage('Lancement des Tests') {
            steps {
                script {
                    def testExists = sh(script: "[ -f vendor/bin/phpunit ] && echo 'exists'", returnStdout: true).trim()
                    if (testExists != 'exists') {
                        error "❌ Aucun test trouvé ! Échec du déploiement."
                    } else {
                        try {
                            sh 'vendor/bin/phpunit --coverage-html coverage-report --coverage-text --log-junit test-results.xml'
                        } catch (Exception e) {
                            // En cas d'échec, envoyer un e-mail avec la raison
                            emailext (
                                to: "${env.EMAIL_RECIPIENTS}",
                                subject: "Échec du build : Tests échoués",
                                body: """
                                    Les tests ont échoué.
                                    Raison de l'échec : ${e.getMessage()}
                                """,
                                from: 'stephanietakam1@gmail.com',
                                mimeType: 'text/plain'
                            )
                            error "❌ Tests échoués. Consultez l'e-mail pour plus de détails."
                        }
                    }
                }
                junit 'test-results.xml'  // Intégration avec Test Results Analyzer
            }
        }

        // Étape 5 : Vérification de la couverture de code
        stage('Vérification de la couverture de code') {
            steps {
                script {
                    // Lire le rapport de couverture de code
                    def coverageReport = readFile('coverage-report/index.html')
                    def coveragePercentage = (coverageReport =~ /(\d+(\.\d+)?%)/)[0][1].replace('%', '').toDouble()

                    if (coveragePercentage < env.COVERAGE_THRESHOLD.toDouble()) {
                        // En cas de couverture insuffisante, envoyer un e-mail avec le lien vers le rapport
                        emailext (
                            to: "${env.EMAIL_RECIPIENTS}",
                            subject: "Échec du build : Couverture de code insuffisante",
                            body: """
                                La couverture de code est insuffisante.
                                Couverture actuelle : ${coveragePercentage}%
                                Seuil requis : ${env.COVERAGE_THRESHOLD}%
                                Consultez le rapport de couverture : https://cf19-2c0f-2a80-37-a010-d50f-79d9-5aa1-7929.ngrok-free.app/jenkins/job/${env.JOB_NAME}/${env.BUILD_NUMBER}/artifact/coverage-report/index.html
                            """,
                            from: 'stephanietakam1@gmail.com',
                            mimeType: 'text/plain'
                        )
                        error "❌ Couverture de code insuffisante. Consultez l'e-mail pour plus de détails."
                    }
                }
            }
        }

        // Étape 6 : Déploiement
        stage('Deploiement') {
            // when {
            //     expression { currentBuild.result == null || currentBuild.result == 'SUCCESS' }
            // }
            steps {
            //     sh 'docker compose down '
            //     sh 'docker compose build'
            //     sh 'docker compose up -d'
            echo 'deploiement'
            }
            
        }
    }

    // Post-actions
    post {
        success {
            // Envoyer un e-mail en cas de succès
            emailext (
                to: "${env.EMAIL_RECIPIENTS}",
                subject: "Succès du build",
                body: """
                    Le build a réussi.
                    Consultez les détails du build : ${env.BUILD_URL}
                """,
                from: 'stephanietakam1@gmail.com',
                mimeType: 'text/plain'
            )
            sh "git rev-parse HEAD > last_successful_commit.txt"
        }
        failure {
            // En cas d'échec, un e-mail a déjà été envoyé dans les étapes précédentes
            echo "Build échoué. Consultez les e-mails pour plus de détails."
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
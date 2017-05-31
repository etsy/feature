pipeline {
  agent any
  stages {
    stage('composer_install') {
      steps {
        sh '''PATH=$PATH:/bin:/usr/local/bin:/usr/bin
composer install'''
      }
    }
    stage('php_lint') {
      steps {
        sh 'find . -name "*.php" -print0 | xargs -0 -n1 php -l'
      }
    }
    stage('phpunit') {
      steps {
        sh 'phpunit'
      }
    }
    stage('cleanup') {
      steps {
        deleteDir()
      }
    }
  }
}
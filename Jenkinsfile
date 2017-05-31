pipeline {
  agent any
  stages {
    stage('composer_install') {
      steps {
        parallel(
          "composer_install": {
            sh 'composer install'
            
          },
          "php_lint": {
            sh 'find . -name "*.php" -print0 | xargs -0 -n1 php -l'
            
          }
        )
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
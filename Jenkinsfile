pipeline {
  agent any
  stages {
    stage('composer_install') {
      steps {
        parallel(
          "composer_install": {
            sh '''PATH=$PATH:/bin:/usr/local/bin:/usr/bin
composer install'''
            
          },
          "php_lint": {
            sh 'find . -name "*.php" -print0 | xargs -0 -n1 php -l'
            
          }
        )
      }
    }
    stage('phpunit') {
      steps {
        sh '''PATH=$PATH:/bin:/usr/local/bin:/usr/bin:/home/pabloi/.config/composer/vendor/bin/
phpunit'''
      }
    }
    stage('cleanup') {
      steps {
        deleteDir()
      }
    }
  }
}
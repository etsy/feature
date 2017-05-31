pipeline {
  agent any
  stages {
    stage('Build') {
      steps {
        sh 'git clone https://github.com/cafemedia/feature'
      }
    }
    stage('Composer install') {
      steps {
        sh 'composer install'
      }
    }
    stage('') {
      steps {
        sh 'phpunit'
      }
    }
  }
}
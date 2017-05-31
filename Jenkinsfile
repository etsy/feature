node {
    stage('Build') {
        sh 'git clone https://github.com/cafemedia/feature'
    }
    
    stage("composer_install") {
        sh 'composer install'
    }

    stage("php_lint") {
        sh 'find . -name "*.php" -print0 | xargs -0 -n1 php -l'
    }

    stage("phpunit") {
        sh 'phpunit'
    }

    stage('cleanup') {
        deleteDir()
    }    
}

pipeline {
  agent any

  environment {
    NAMESPACE = "linux-monitoring"

    UI_IMAGE = "pratikrastogi/linux-monitoring"
    COLLECTOR_IMAGE = "pratikrastogi/linux-monitor-collector"

    // Date-based tag (same as your script)
    UI_TAG = sh(script: "date +%Y%m%d%H%M", returnStdout: true).trim()
    COLLECTOR_TAG = sh(script: "date +%Y%m%d%H%M", returnStdout: true).trim()
  }

  stages {

    stage('Checkout Code') {
      steps {
        checkout scm
      }
    }

    stage('Detect Changes') {
      steps {
        script {
          def changes = sh(
            script: "git diff --name-only HEAD~1",
            returnStdout: true
          ).trim()

          echo "Changed files:\n${changes}"

          env.BUILD_UI = changes.contains("ui-php/") ? "true" : "false"
          env.BUILD_COLLECTOR = changes.contains("collector/") ? "true" : "false"
        }
      }
    }

    stage('Build & Deploy UI') {
      when {
        expression { env.BUILD_UI == "true" }
      }
      steps {
        echo "🔧 Building PHP UI image..."

        sh """
          docker build -t ${UI_IMAGE}:${UI_TAG} ui-php/
        """

        withCredentials([usernamePassword(
          credentialsId: 'dockerhub-creds',
          usernameVariable: 'generationpratik@gmail.com',
          passwordVariable: 'Docker@123!'
        )]) {
          sh """
            docker login -u $DOCKER_USER -p $DOCKER_PASS
            docker push ${UI_IMAGE}:${UI_TAG}
          """
        }

        echo "🚢 Deploying PHP UI..."

        sh """
          kubectl set image deployment/php-ui \
          php-ui=${UI_IMAGE}:${UI_TAG} \
          -n ${NAMESPACE}
        """
      }
    }

    stage('Build & Deploy Collector') {
      when {
        expression { env.BUILD_COLLECTOR == "true" }
      }
      steps {
        echo "🔧 Building Collector image..."

        sh """
          docker build -t ${COLLECTOR_IMAGE}:${COLLECTOR_TAG} collector/
        """

        withCredentials([usernamePassword(
          credentialsId: 'dockerhub-creds',
          usernameVariable: 'DOCKER_USER',
          passwordVariable: 'DOCKER_PASS'
        )]) {
          sh """
            docker login -u $DOCKER_USER -p $DOCKER_PASS
            docker push ${COLLECTOR_IMAGE}:${COLLECTOR_TAG}
          """
        }

        echo "🚢 Deploying Collector..."

        sh """
          kubectl set image deployment/collector \
          collector=${COLLECTOR_IMAGE}:${COLLECTOR_TAG} \
          -n ${NAMESPACE}
        """
      }
    }
  }

  post {
    success {
      echo "✅ CI/CD Pipeline completed successfully!"
    }
    failure {
      echo "❌ CI/CD Pipeline failed"
    }
  }
}


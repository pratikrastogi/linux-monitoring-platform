pipeline {
  agent any

  environment {
    NAMESPACE = "linux-monitoring"

    UI_IMAGE        = "pratikrastogi/linux-monitoring"
    COLLECTOR_IMAGE = "pratikrastogi/linux-monitor-collector"
    TERMINAL_IMAGE  = "pratikrastogi/linux-monitor-terminal"
    PROVISION_IMAGE = "pratikrastogi/linux-monitor-provision"
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
            script: "git diff --name-only HEAD~1 || true",
            returnStdout: true
          ).trim()

          echo "Changed files:\n${changes}"

          env.BUILD_UI        = changes.contains("ui-php/") ? "true" : "false"
          env.BUILD_COLLECTOR = changes.contains("collector/") ? "true" : "false"
          env.BUILD_TERMINAL  = changes.contains("terminal-gateway/") ? "true" : "false"
          env.BUILD_PROVISION = changes.contains("provision_worker/") ? "true" : "false"

          env.BUILD_TAG = sh(
            script: "date +%Y%m%d%H%M",
            returnStdout: true
          ).trim()
        }
      }
    }

    /* ================= UI ================= */
    stage('Build & Deploy UI') {
      when { expression { env.BUILD_UI == "true" } }
      steps {
        echo "🔧 Building PHP UI image"

        sh "docker build -t ${UI_IMAGE}:${BUILD_TAG} ui-php/"

        withCredentials([usernamePassword(
          credentialsId: 'dockerhub-creds',
          usernameVariable: 'DOCKER_USER',
          passwordVariable: 'DOCKER_PASS'
        )]) {
          sh """
            docker login -u ${DOCKER_USER} -p ${DOCKER_PASS}
            docker push ${UI_IMAGE}:${BUILD_TAG}
          """
        }

        sh """
          kubectl set image deployment/php-ui \
          php-ui=${UI_IMAGE}:${BUILD_TAG} \
          -n ${NAMESPACE}
        """
      }
    }

    /* ================= COLLECTOR ================= */
    stage('Build & Deploy Collector') {
      when { expression { env.BUILD_COLLECTOR == "true" } }
      steps {
        echo "🔧 Building Collector image"

        sh "docker build -t ${COLLECTOR_IMAGE}:${BUILD_TAG} collector/"

        withCredentials([usernamePassword(
          credentialsId: 'dockerhub-creds',
          usernameVariable: 'DOCKER_USER',
          passwordVariable: 'DOCKER_PASS'
        )]) {
          sh """
            docker login -u ${DOCKER_USER} -p ${DOCKER_PASS}
            docker push ${COLLECTOR_IMAGE}:${BUILD_TAG}
          """
        }

        sh """
          kubectl set image deployment/collector \
          collector=${COLLECTOR_IMAGE}:${BUILD_TAG} \
          -n ${NAMESPACE}
        """
      }
    }

    /* ================= TERMINAL GATEWAY ================= */
    stage('Build & Deploy Terminal Gateway') {
      when { expression { env.BUILD_TERMINAL == "true" } }
      steps {
        echo "🔧 Building Terminal Gateway image"

        sh "docker build -t ${TERMINAL_IMAGE}:${BUILD_TAG} terminal-gateway/"

        withCredentials([usernamePassword(
          credentialsId: 'dockerhub-creds',
          usernameVariable: 'DOCKER_USER',
          passwordVariable: 'DOCKER_PASS'
        )]) {
          sh """
            docker login -u ${DOCKER_USER} -p ${DOCKER_PASS}
            docker push ${TERMINAL_IMAGE}:${BUILD_TAG}
          """
        }

        sh """
          kubectl set image deployment/terminal-gateway \
          terminal-gateway=${TERMINAL_IMAGE}:${BUILD_TAG} \
          -n ${NAMESPACE}
        """
      }
    }

    /* ================= PROVISION WORKER ================= */
    stage('Build & Deploy Provision Worker') {
      when { expression { env.BUILD_PROVISION == "true" } }
      steps {
        echo "🔧 Building Provision Worker image"

        sh "docker build -t ${PROVISION_IMAGE}:${BUILD_TAG} provision_worker/"

        withCredentials([usernamePassword(
          credentialsId: 'dockerhub-creds',
          usernameVariable: 'DOCKER_USER',
          passwordVariable: 'DOCKER_PASS'
        )]) {
          sh """
            docker login -u ${DOCKER_USER} -p ${DOCKER_PASS}
            docker push ${PROVISION_IMAGE}:${BUILD_TAG}
          """
        }

        sh """
          kubectl set image deployment/provision-worker \
          provision-worker=${PROVISION_IMAGE}:${BUILD_TAG} \
          -n ${NAMESPACE}
        """
      }
    }
  }

  post {
    success {
      echo "✅ CI/CD Pipeline completed successfully"
    }
    failure {
      echo "❌ CI/CD Pipeline failed"
    }
  }
}


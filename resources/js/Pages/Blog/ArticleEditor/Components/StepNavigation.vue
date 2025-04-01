<template>
  <div class="step-navigation">
    <div class="progress-bar">
      <div 
        class="progress-indicator" 
        :style="{ width: `${(currentStep / totalSteps) * 100}%` }"
      ></div>
    </div>
    
    <div class="steps-container">
      <div 
        v-for="step in totalSteps" 
        :key="step"
        class="step"
        :class="{
          'active': step === currentStep,
          'completed': step < currentStep,
          'upcoming': step > currentStep
        }"
        @click="canNavigateToStep(step) && navigateToStep(step)"
      >
        <div class="step-circle">
          <div v-if="step < currentStep" class="step-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
              <path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 01.208 1.04l-9 13.5a.75.75 0 01-1.154.114l-6-6a.75.75 0 011.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 011.04-.208z" clip-rule="evenodd" />
            </svg>
          </div>
          <div v-else>{{ step }}</div>
        </div>
        <div class="step-label">{{ getStepLabel(step) }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useArticleEditorStore } from '@/stores/articleEditor';

const props = defineProps({
  currentStep: {
    type: Number,
    required: true
  },
  totalSteps: {
    type: Number,
    default: 3
  },
  canNavigate: {
    type: Function,
    default: () => true
  }
});

const emit = defineEmits(['navigate']);

const editorStore = useArticleEditorStore();

const stepLabels = {
  1: '创作内容',
  2: '分类标签',
  3: '发布设置'
};

const getStepLabel = (step) => stepLabels[step] || `步骤 ${step}`;

const canNavigateToStep = (step) => {
  // 已完成的步骤或当前步骤可以直接导航
  if (step < props.currentStep || step === props.currentStep) {
    return true;
  }
  
  // 下一步需要检查当前步骤是否有效
  if (step === props.currentStep + 1) {
    return props.canNavigate(props.currentStep);
  }
  
  // 不允许跳过多个步骤
  return false;
};

const navigateToStep = (step) => {
  if (canNavigateToStep(step)) {
    emit('navigate', step);
  }
};
</script>

<style scoped>
.step-navigation {
  display: flex;
  flex-direction: column;
  width: 100%;
  margin-bottom: 1.5rem;
}

.progress-bar {
  height: 4px;
  background-color: #e5e7eb;
  border-radius: 2px;
  margin-bottom: 1rem;
  position: relative;
}

.progress-indicator {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  background-color: #f97316;
  border-radius: 2px;
  transition: width 0.3s ease;
}

.steps-container {
  display: flex;
  justify-content: space-between;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  cursor: pointer;
  transition: all 0.2s ease;
}

.step-circle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  background-color: #e5e7eb;
  color: #6b7280;
  font-weight: 600;
  margin-bottom: 0.5rem;
  transition: all 0.2s ease;
}

.step-label {
  font-size: 0.875rem;
  color: #6b7280;
  transition: all 0.2s ease;
}

.step.active .step-circle {
  background-color: #f97316;
  color: white;
}

.step.active .step-label {
  color: #f97316;
  font-weight: 600;
}

.step.completed .step-circle {
  background-color: #10b981;
  color: white;
}

.step.completed .step-label {
  color: #10b981;
}

.step.upcoming {
  cursor: not-allowed;
  opacity: 0.5;
}

.step-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

@media (max-width: 640px) {
  .step-label {
    font-size: 0.75rem;
  }
  
  .step-circle {
    width: 1.75rem;
    height: 1.75rem;
  }
}
</style> 
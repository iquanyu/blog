<script setup>
import { ref, onMounted, watch } from 'vue'
import { Chart } from 'chart.js/auto'

const props = defineProps({
    data: {
        type: Array,
        required: true
    }
})

const chartRef = ref(null)
let chart = null

const createChart = () => {
    const ctx = chartRef.value.getContext('2d')
    
    // 处理数据
    const dates = props.data.map(item => new Date(item.date).toLocaleDateString('zh-CN'))
    const counts = props.data.map(item => item.count)

    // 销毁现有图表
    if (chart) {
        chart.destroy()
    }

    // 创建新图表
    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: '发布文章数',
                data: counts,
                borderColor: '#f97316',
                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    })
}

// 监听数据变化重新渲染图表
watch(() => props.data, createChart, { deep: true })

onMounted(() => {
    createChart()
})
</script>

<template>
    <canvas ref="chartRef" class="w-full h-full"></canvas>
</template> 
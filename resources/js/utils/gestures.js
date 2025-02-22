export function setupGestures(element, handlers) {
    let touchStartX = 0
    let touchStartY = 0
    const minSwipeDistance = 50

    element.addEventListener('touchstart', (e) => {
        touchStartX = e.touches[0].clientX
        touchStartY = e.touches[0].clientY
    })

    element.addEventListener('touchend', (e) => {
        const touchEndX = e.changedTouches[0].clientX
        const touchEndY = e.changedTouches[0].clientY
        
        const deltaX = touchEndX - touchStartX
        const deltaY = touchEndY - touchStartY
        
        // 判断是水平滑动还是垂直滑动
        if (Math.abs(deltaX) > Math.abs(deltaY)) {
            if (Math.abs(deltaX) > minSwipeDistance) {
                if (deltaX > 0 && handlers.onSwipeRight) {
                    handlers.onSwipeRight()
                } else if (deltaX < 0 && handlers.onSwipeLeft) {
                    handlers.onSwipeLeft()
                }
            }
        } else {
            if (Math.abs(deltaY) > minSwipeDistance) {
                if (deltaY > 0 && handlers.onSwipeDown) {
                    handlers.onSwipeDown()
                } else if (deltaY < 0 && handlers.onSwipeUp) {
                    handlers.onSwipeUp()
                }
            }
        }
    })
} 
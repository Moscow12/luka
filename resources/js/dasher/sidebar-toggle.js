/**
 * Sidebar Toggle Module
 * Handles expanding/collapsing sidebar by toggling classes on HTML element
 */

(() => {
    'use strict'

    const getSidebarState = () => localStorage.getItem('sidebarExpanded')
    const setSidebarState = (isExpanded) => localStorage.setItem('sidebarExpanded', isExpanded)

    const toggleSidebar = () => {
        const html = document.documentElement
        const isCurrentlyExpanded = html.classList.contains('expanded')

        if (isCurrentlyExpanded) {
            // Collapse sidebar
            html.classList.remove('expanded')
            html.classList.add('collapsed')
            setSidebarState('false')
        } else {
            // Expand sidebar
            html.classList.remove('collapsed')
            html.classList.add('expanded')
            setSidebarState('true')
        }
    }

    const setupSidebarToggle = () => {
        const toggleButton = document.querySelector('.sidebar-toggle')

        if (toggleButton) {
            toggleButton.addEventListener('click', (e) => {
                e.preventDefault()
                toggleSidebar()
            })
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupSidebarToggle)
    } else {
        setupSidebarToggle()
    }
})()

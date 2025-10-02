/**
 * Theme Switcher Module
 * Handles light/dark/auto theme switching with localStorage persistence
 */

(() => {
    'use strict'

    const getStoredTheme = () => localStorage.getItem('theme')
    const setStoredTheme = theme => localStorage.setItem('theme', theme)

    const getPreferredTheme = () => {
        const storedTheme = getStoredTheme()
        if (storedTheme) {
            return storedTheme
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    }

    const setTheme = theme => {
        let actualTheme = theme
        if (theme === 'auto') {
            actualTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
            document.documentElement.setAttribute('data-bs-theme', actualTheme)
        } else {
            document.documentElement.setAttribute('data-bs-theme', theme)
        }

        // Set theme attribute on body tag only when dark
        if (actualTheme === 'dark') {
            document.body.setAttribute('theme', 'dark')
        } else {
            document.body.removeAttribute('theme')
        }
    }

    const showActiveTheme = (theme, focus = false) => {
        const themeSwitchers = document.querySelectorAll('[data-theme-toggle]')

        themeSwitchers.forEach(switcher => {
            const activeThemeIcon = switcher.querySelector('.theme-icon-active .theme-icon')
            const btnToActive = switcher.parentElement.querySelector(`[data-bs-theme-value="${theme}"]`)

            if (!btnToActive || !activeThemeIcon) return

            const iconOfActiveBtn = btnToActive.querySelector('.theme-icon').classList[2]

            // Update all theme buttons in this switcher's dropdown
            switcher.parentElement.querySelectorAll('[data-bs-theme-value]').forEach(element => {
                element.classList.remove('active')
                element.setAttribute('aria-pressed', 'false')
            })

            btnToActive.classList.add('active')
            btnToActive.setAttribute('aria-pressed', 'true')

            // Remove all possible icon classes (both bi and ti)
            activeThemeIcon.classList.remove('bi-sun-fill', 'bi-moon-stars-fill', 'bi-circle-half', 'ti-sun', 'ti-moon-stars', 'ti-circle-half-2')
            activeThemeIcon.classList.add(iconOfActiveBtn)

            const themeSwitcherLabel = `Toggle theme (${theme})`
            switcher.setAttribute('aria-label', themeSwitcherLabel)

            if (focus) {
                switcher.focus()
            }
        })
    }

    // Initialize theme on page load
    const initTheme = () => {
        const preferredTheme = getPreferredTheme()
        setTheme(preferredTheme)
        showActiveTheme(preferredTheme)
    }

    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        const storedTheme = getStoredTheme()
        if (storedTheme !== 'light' && storedTheme !== 'dark') {
            setTheme(getPreferredTheme())
        }
    })

    // Setup event listeners for theme toggle buttons
    const setupEventListeners = () => {
        document.querySelectorAll('[data-bs-theme-value]').forEach(toggle => {
            toggle.addEventListener('click', () => {
                const theme = toggle.getAttribute('data-bs-theme-value')
                setStoredTheme(theme)
                setTheme(theme)
                showActiveTheme(theme, true)
            })
        })
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initTheme()
            setupEventListeners()
        })
    } else {
        initTheme()
        setupEventListeners()
    }
})()

import { defineStore } from 'pinia'
import notificationService from '../services/notification.js'

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    notifications: [],
    unreadCount: 0,
    loading: false,
    error: null,
    currentPage: 1,
    totalPages: 1,
    hasMore: true
  }),

  getters: {
    unreadNotifications: (state) => {
      return state.notifications.filter(notification => !notification.read_at)
    },
    
    readNotifications: (state) => {
      return state.notifications.filter(notification => notification.read_at)
    }
  },

  actions: {
    async fetchNotifications(page = 1) {
      this.loading = true
      this.error = null
      
      try {
        const response = await notificationService.getNotifications(page)
        
        if (page === 1) {
          this.notifications = response.notifications
        } else {
          this.notifications.push(...response.notifications)
        }
        
        this.currentPage = response.pagination.current_page
        this.totalPages = response.pagination.last_page
        this.hasMore = this.currentPage < this.totalPages
        
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch notifications'
        console.error('Error fetching notifications:', error)
      } finally {
        this.loading = false
      }
    },

    async fetchUnreadCount() {
      try {
        const response = await notificationService.getUnreadCount()
        this.unreadCount = response.unread_count
      } catch (error) {
        console.error('Error fetching unread count:', error)
      }
    },

    async markAsRead(notificationId) {
      try {
        await notificationService.markAsRead(notificationId)
        
        // Update local state
        const notification = this.notifications.find(n => n.id === notificationId)
        if (notification && !notification.read_at) {
          notification.read_at = new Date().toISOString()
          this.unreadCount = Math.max(0, this.unreadCount - 1)
        }
        
      } catch (error) {
        console.error('Error marking notification as read:', error)
      }
    },

    async markAllAsRead() {
      try {
        await notificationService.markAllAsRead()
        
        // Update local state
        this.notifications.forEach(notification => {
          if (!notification.read_at) {
            notification.read_at = new Date().toISOString()
          }
        })
        this.unreadCount = 0
        
      } catch (error) {
        console.error('Error marking all notifications as read:', error)
      }
    },

    async deleteNotification(notificationId) {
      try {
        await notificationService.deleteNotification(notificationId)
        
        // Update local state
        const index = this.notifications.findIndex(n => n.id === notificationId)
        if (index !== -1) {
          const notification = this.notifications[index]
          if (!notification.read_at) {
            this.unreadCount = Math.max(0, this.unreadCount - 1)
          }
          this.notifications.splice(index, 1)
        }
        
      } catch (error) {
        console.error('Error deleting notification:', error)
      }
    },

    addNotification(notification) {
      // Add new notification to the beginning of the list
      this.notifications.unshift(notification)
      
      // Increment unread count if notification is unread
      if (!notification.read_at) {
        this.unreadCount++
      }
      
      // Show browser notification
      notificationService.showBrowserNotification(
        notification.data?.title || 'New Notification',
        notification.data?.message || 'You have a new notification'
      )
      
      // Show toast notification (if available)
      if (window.showToast) {
        window.showToast({
          title: notification.data?.title || 'New Notification',
          message: notification.data?.message || 'You have a new notification',
          type: 'info',
          duration: 6000
        })
      }
    },

    async requestNotificationPermission() {
      return await notificationService.requestNotificationPermission()
    },

    clearError() {
      this.error = null
    }
  }
})
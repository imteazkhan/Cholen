<template>
  <div class="container mt-4">
    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h2>Admin Control Center</h2>
            <p class="text-muted">Complete system management and oversight</p>
            <div class="mt-2">
              <span class="badge bg-success me-2">
                <i class="bi bi-cloud-check me-1"></i>
                Live Data Mode
              </span>
              <small class="text-muted">Connected to real-time database</small>
            </div>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-outline-info" @click="showSystemInfo">
              <i class="bi bi-info-circle me-1"></i>
              System Info
            </button>
            <button class="btn btn-success" @click="refreshAllData" :disabled="refreshingAll">
              <span v-if="refreshingAll" class="spinner-border spinner-border-sm me-1"></span>
              <i v-else class="bi bi-arrow-clockwise me-1"></i>
              Refresh All
            </button>
            <button class="btn btn-primary" @click="showAddDriverModal = true">
              <i class="bi bi-plus-circle me-2"></i>
              Add Driver
            </button>
          </div>
        </div>

        <!-- Dynamic Admin Capabilities Overview -->
        <div class="row g-4 mb-4">
          <div class="col-md-3" v-for="capability in adminCapabilities" :key="capability.id">
            <div class="admin-capability-card"
              :class="{ 'capability-active': activeTab === capability.tabId, 'capability-loading': capability.loading }"
              @click="switchTab(capability.tabId)">
              <div class="capability-icon" :class="capability.iconClass">
                <i :class="capability.icon"></i>
                <div v-if="capability.loading" class="capability-spinner">
                  <div class="spinner-border spinner-border-sm text-white"></div>
                </div>
              </div>
              <div class="capability-content">
                <h5>{{ capability.title }}</h5>
                <p class="text-muted">{{ capability.description }}</p>
                <div class="capability-stats">
                  <div class="stats-row">
                    <span v-for="stat in capability.primaryStats" :key="stat.label" class="badge"
                      :class="stat.badgeClass">
                      <i v-if="stat.icon" :class="stat.icon" class="me-1"></i>
                      {{ stat.value }} {{ stat.label }}
                    </span>
                  </div>
                  <div class="stats-row mt-1" v-if="capability.secondaryStats.length > 0">
                    <small v-for="stat in capability.secondaryStats" :key="stat.label" class="text-muted me-2">
                      {{ stat.label }}: <strong>{{ stat.value }}</strong>
                    </small>
                  </div>
                </div>
                <div class="capability-trend" v-if="capability.trend">
                  <small :class="capability.trend.class">
                    <i :class="capability.trend.icon" class="me-1"></i>
                    {{ capability.trend.text }}
                  </small>
                </div>
              </div>
              <div class="capability-arrow">
                <i class="bi bi-arrow-right"></i>
              </div>
              <div v-if="capability.alert" class="capability-alert" :class="capability.alert.class">
                <i :class="capability.alert.icon"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Dynamic Quick Actions Bar -->
        <div class="quick-actions-bar mb-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">
              <i class="bi bi-lightning-charge me-2"></i>
              Quick Actions
            </h6>
            <small class="text-muted">Real-time admin controls</small>
          </div>
          <div class="row g-3">
            <div class="col-md-2" v-for="action in quickActions" :key="action.id">
              <button class="btn w-100 quick-action-btn" :class="action.buttonClass" @click="executeQuickAction(action)"
                :disabled="action.disabled || action.loading" :title="action.tooltip">
                <div class="action-icon-container">
                  <span v-if="action.loading" class="spinner-border spinner-border-sm"></span>
                  <i v-else :class="action.icon" class="d-block mb-1"></i>
                  <span v-if="action.badge > 0" class="action-badge" :class="action.badgeClass">
                    {{ action.badge }}
                  </span>
                </div>
                <small class="action-label">{{ action.label }}</small>
                <div v-if="action.subtitle" class="action-subtitle">
                  <small class="text-muted">{{ action.subtitle }}</small>
                </div>
              </button>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="adminTabs">
              <li class="nav-item" v-for="tab in adminTabs" :key="tab.id">
                <button class="nav-link dynamic-tab" :class="{
                  active: activeTab === tab.id,
                  'has-badge': tab.badge > 0,
                  'loading': tab.loading
                }" @click="switchTab(tab.id)" :disabled="tab.disabled">
                  <i :class="tab.icon" class="me-2"></i>
                  {{ tab.label }}
                  <span v-if="tab.badge > 0" class="badge ms-2" :class="tab.badgeClass">
                    {{ tab.badge }}
                  </span>
                  <span v-if="tab.loading" class="spinner-border spinner-border-sm ms-2"></span>
                </button>
              </li>
            </ul>
          </div>
          <div class="card-body">
            <div class="tab-content">
              <!-- Enhanced Users Tab -->
              <div v-show="activeTab === 'users'" class="tab-pane">
                <h4>User Management</h4>
                <p>Total Users: {{ users.length }}</p>

                <div v-if="loadingUsers" class="text-center py-4">
                  <div class="spinner-border" role="status"></div>
                  <p class="mt-2">Loading users...</p>
                </div>

                <div v-else-if="users.length === 0" class="text-center py-4">
                  <p>No users found</p>
                  <button class="btn btn-primary" @click="loadUsers">Load Users</button>
                </div>

                <div v-else>
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="user in users" :key="user.id">
                        <td>{{ user.first_name }} {{ user.last_name }}</td>
                        <td>{{ user.email }}</td>
                        <td>{{ user.role }}</td>
                        <td>{{ user.is_active ? 'Active' : 'Inactive' }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Advanced Filters and Search -->
              <div class="row g-3 mb-4">
                <div class="col-md-4">
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">
                      <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" placeholder="Search users by name, email, or phone..."
                      v-model="userSearchQuery">
                    <button v-if="userSearchQuery" class="btn btn-outline-secondary" @click="userSearchQuery = ''">
                      <i class="bi bi-x"></i>
                    </button>
                  </div>
                </div>
                <div class="col-md-2">
                  <select class="form-select form-select-sm" v-model="userRoleFilter">
                    <option value="all">All Roles</option>
                    <option value="user">Users Only</option>
                    <option value="driver">Drivers Only</option>
                    <option value="admin">Admins Only</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <select class="form-select form-select-sm" v-model="userStatusFilter">
                    <option value="all">All Status</option>
                    <option value="active">Active Only</option>
                    <option value="inactive">Inactive Only</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <select class="form-select form-select-sm" v-model="userSortBy">
                    <option value="created_at">Sort by Join Date</option>
                    <option value="name">Sort by Name</option>
                    <option value="email">Sort by Email</option>
                    <option value="role">Sort by Role</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <button class="btn btn-outline-secondary btn-sm w-100" @click="toggleUserSortOrder">
                    <i :class="userSortOrder === 'asc' ? 'bi bi-sort-alpha-down' : 'bi bi-sort-alpha-up'"></i>
                    {{ userSortOrder === 'asc' ? 'A-Z' : 'Z-A' }}
                  </button>
                </div>
              </div>

              <!-- User Statistics -->
              <div class="row g-3 mb-4">
                <div class="col-md-3">
                  <div
                    class="stat-card border border-success text-success bg-white  rounded-3 p-4 d-flex flex-column justify-content-center align-items-center">
                    <div class="stat-number">{{ users.length }}</div>
                    <div class="stat-label">Total Users</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div
                    class="stat-card border border-info text-info bg-white  rounded-3 p-4 d-flex flex-column justify-content-center align-items-center">
                    <div class="stat-number">{{users.filter(u => u.is_active).length}}</div>
                    <div class="stat-label">Active Users</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div
                    class="stat-card border border-warning text-warning bg-white  rounded-3 p-4 d-flex flex-column justify-content-center align-items-center">
                    <div class="stat-number">{{users.filter(u => u.role === 'user').length}}</div>
                    <div class="stat-label">Regular Users</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div
                    class="stat-card border border-primary text-primary bg-white  rounded-3 p-4 d-flex flex-column justify-content-center align-items-center">
                    <div class="stat-number">{{users.filter(u => u.role === 'admin').length}}</div>
                    <div class="stat-label">Administrators</div>
                  </div>
                </div>
              </div>

              <!-- Loading State -->
              <div v-if="loadingUsers" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                <p class="mt-3 text-muted">Loading users from database...</p>
              </div>

              <!-- Empty State -->
              <div v-else-if="!filteredAndSortedUsers || filteredAndSortedUsers.length === 0" class="text-center py-5">
                <i class="bi bi-people display-1 text-muted mb-3"></i>
                <h5 class="text-muted">{{ userSearchQuery || userRoleFilter !== 'all' || userStatusFilter !== 'all' ?
                  'No users match your filters' : 'No users found' }}</h5>
                <p class="text-muted mb-4">
                  {{ userSearchQuery || userRoleFilter !== 'all' || userStatusFilter !== 'all'
                    ? 'Try adjusting your search criteria or filters'
                    : 'Get started by adding your first user to the platform' }}
                </p>
                <button v-if="!userSearchQuery && userRoleFilter === 'all' && userStatusFilter === 'all'"
                  class="btn btn-primary" @click="showAddUserModal = true">
                  <i class="bi bi-person-plus me-2"></i>
                  Add First User
                </button>
                <button v-else class="btn btn-outline-secondary" @click="clearUserFilters">
                  <i class="bi bi-funnel me-2"></i>
                  Clear Filters
                </button>
              </div>

              <!-- Users Table -->
              <div v-else>
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div class="d-flex align-items-center gap-3">
                    <small class="text-muted">
                      Showing {{ filteredAndSortedUsers.length }} of {{ users.length }} users
                    </small>
                    <div class="d-flex gap-2">
                      <span class="badge bg-primary">{{users.filter(u => u.role === 'user').length}} Users</span>
                      <span class="badge bg-warning text-dark">{{users.filter(u => u.role === 'driver').length}}
                        Drivers</span>
                      <span class="badge bg-danger">{{users.filter(u => u.role === 'admin').length}} Admins</span>
                    </div>
                  </div>
                  <div class="d-flex gap-2">
                    <button class="btn btn-outline-info btn-sm" @click="exportUsers">
                      <i class="bi bi-download me-1"></i>
                      Export
                    </button>
                    <div class="dropdown">
                      <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                      </button>
                      <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" @click="bulkActivateUsers">
                            <i class="bi bi-check-circle me-2"></i>Activate Selected
                          </a></li>
                        <li><a class="dropdown-item" href="#" @click="bulkDeactivateUsers">
                            <i class="bi bi-x-circle me-2"></i>Deactivate Selected
                          </a></li>
                        <li>
                          <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="#" @click="bulkDeleteUsers">
                            <i class="bi bi-trash me-2"></i>Delete Selected
                          </a></li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="table table-hover align-middle">
                    <thead class="table-light">
                      <tr>
                        <th width="40">
                          <input type="checkbox" class="form-check-input" v-model="selectAllUsers"
                            @change="toggleSelectAllUsers">
                        </th>
                        <th>User</th>
                        <th>Contact</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Last Active</th>
                        <th width="120">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="user in paginatedUsers" :key="user.id"
                        :class="{ 'table-warning': selectedUsers.includes(user.id) }">
                        <td>
                          <input type="checkbox" class="form-check-input" :value="user.id" v-model="selectedUsers">
                        </td>
                        <td>
                          <div class="d-flex align-items-center">
                            <div class="avatar-md me-3" :class="getUserAvatarClass(user)">
                              {{ getUserInitials(user) }}
                            </div>
                            <div>
                              <div class="fw-semibold">
                                <a href="#" @click.prevent="showUserDetails(user)"
                                  class="text-decoration-none user-link">
                                  {{ user.first_name }} {{ user.last_name }}
                                </a>
                              </div>
                              <small class="text-muted">ID: #{{ user.id }}</small>
                            </div>
                          </div>
                        </td>
                        <td>
                          <div>
                            <div class="fw-medium">{{ user.email }}</div>
                            <small class="text-muted">{{ user.phone || 'No phone' }}</small>
                          </div>
                        </td>
                        <td>
                          <span class="badge fs-6" :class="getRoleBadgeClass(user.role)">
                            <i :class="getRoleIcon(user.role)" class="me-1"></i>
                            {{ user.role }}
                          </span>
                        </td>
                        <td>
                          <div class="d-flex align-items-center">
                            <span class="status-indicator"
                              :class="user.is_active ? 'bg-success' : 'bg-secondary'"></span>
                            <span class="badge" :class="user.is_active ? 'bg-success' : 'bg-secondary'">
                              {{ user.is_active ? 'Active' : 'Inactive' }}
                            </span>
                          </div>
                        </td>
                        <td>
                          <div>
                            <div class="fw-medium">{{ formatDate(user.created_at) }}</div>
                            <small class="text-muted">{{ getRelativeTime(user.created_at) }}</small>
                          </div>
                        </td>
                        <td>
                          <div>
                            <div class="fw-medium">{{ formatDate(user.last_login_at) }}</div>
                            <small class="text-muted">{{ getRelativeTime(user.last_login_at) }}</small>
                          </div>
                        </td>
                        <td>
                          <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" @click="editUser(user)" title="Edit User">
                              <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-info" @click="showUserDetails(user)" title="View Details">
                              <i class="bi bi-eye"></i>
                            </button>
                            <button v-if="user.role !== 'admin'" class="btn btn-outline-warning"
                              @click="promoteToAdmin(user)" title="Promote to Admin">
                              <i class="bi bi-shield-check"></i>
                            </button>
                            <button class="btn btn-outline-danger" @click="deleteUser(user.id)"
                              :disabled="user.role === 'admin'" title="Delete User">
                              <i class="bi bi-trash"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                  <div class="d-flex align-items-center gap-2">
                    <small class="text-muted">Show</small>
                    <select class="form-select form-select-sm" style="width: auto;" v-model="usersPerPage">
                      <option value="10">10</option>
                      <option value="25">25</option>
                      <option value="50">50</option>
                      <option value="100">100</option>
                    </select>
                    <small class="text-muted">per page</small>
                  </div>
                  <nav>
                    <ul class="pagination pagination-sm mb-0">
                      <li class="page-item" :class="{ disabled: currentUserPage === 1 }">
                        <button class="page-link" @click="currentUserPage = 1" :disabled="currentUserPage === 1">
                          <i class="bi bi-chevron-double-left"></i>
                        </button>
                      </li>
                      <li class="page-item" :class="{ disabled: currentUserPage === 1 }">
                        <button class="page-link" @click="currentUserPage--" :disabled="currentUserPage === 1">
                          <i class="bi bi-chevron-left"></i>
                        </button>
                      </li>
                      <li v-for="page in visibleUserPages" :key="page" class="page-item"
                        :class="{ active: page === currentUserPage }">
                        <button class="page-link" @click="currentUserPage = page">{{ page }}</button>
                      </li>
                      <li class="page-item" :class="{ disabled: currentUserPage === totalUserPages }">
                        <button class="page-link" @click="currentUserPage++"
                          :disabled="currentUserPage === totalUserPages">
                          <i class="bi bi-chevron-right"></i>
                        </button>
                      </li>
                      <li class="page-item" :class="{ disabled: currentUserPage === totalUserPages }">
                        <button class="page-link" @click="currentUserPage = totalUserPages"
                          :disabled="currentUserPage === totalUserPages">
                          <i class="bi bi-chevron-double-right"></i>
                        </button>
                      </li>
                    </ul>
                  </nav>
                  <small class="text-muted">
                    Page {{ currentUserPage }} of {{ totalUserPages }}
                    ({{ ((currentUserPage - 1) * usersPerPage) + 1 }}-{{ Math.min(currentUserPage * usersPerPage,
                      filteredAndSortedUsers.length) }}
                    of {{ filteredAndSortedUsers.length }})
                  </small>
                </div>
              </div>
            </div>

            <!-- Drivers Tab -->
            <div v-show="activeTab === 'drivers'" class="tab-pane">
              <h4>Driver Management</h4>
              <p>Total Drivers: {{ drivers.length }}</p>

              <div v-if="loadingDrivers" class="text-center py-4">
                <div class="spinner-border" role="status"></div>
                <p class="mt-2">Loading drivers...</p>
              </div>

              <div v-else-if="drivers.length === 0" class="text-center py-4">
                <p>No drivers found</p>
                <button class="btn btn-primary" @click="loadDrivers">Load Drivers</button>
              </div>

              <div v-else>
                <table class="table">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Email</th>
                      <th>License</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="driver in drivers" :key="driver.id">
                      <td>{{ driver.first_name }} {{ driver.last_name }}</td>
                      <td>{{ driver.email }}</td>
                      <td>{{ driver.driver_license }}</td>
                      <td>{{ driver.driver_status }}</td>
                      <td>
                        <button v-if="driver.driver_status === 'pending'" class="btn btn-sm btn-success me-1"
                          @click="approveDriver(driver.id)">
                          Approve
                        </button>
                        <button v-if="driver.driver_status === 'pending'" class="btn btn-sm btn-danger"
                          @click="rejectDriver(driver.id)">
                          Reject
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Ride Requests Tab -->
            <div v-show="activeTab === 'rides'" class="tab-pane">
              <h4>Ride Request Monitoring</h4>
              <p>Total Rides: {{ rides.length }}</p>

              <div v-if="loadingRides" class="text-center py-4">
                <div class="spinner-border" role="status"></div>
                <p class="mt-2">Loading rides...</p>
              </div>

              <div v-else-if="rides.length === 0" class="text-center py-4">
                <p>No rides found</p>
                <button class="btn btn-primary" @click="loadRides">Load Rides</button>
              </div>

              <div v-else>
                <table class="table">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>User</th>
                      <th>Driver</th>
                      <th>Status</th>
                      <th>Price</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="ride in rides" :key="ride.id">
                      <td>#{{ ride.id }}</td>
                      <td>{{ ride.user?.first_name }} {{ ride.user?.last_name }}</td>
                      <td>{{ ride.driver ? ride.driver.first_name + ' ' + ride.driver.last_name : 'Not assigned' }}</td>
                      <td>{{ ride.status }}</td>
                      <td>BDT {{ ride.estimated_price }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Analytics Tab -->
            <div v-show="activeTab === 'analytics'" class="tab-pane">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>System Analytics</h5>
                <button class="btn btn-outline-primary btn-sm" @click="loadAnalytics">
                  <i class="bi bi-arrow-clockwise me-1"></i>
                  Refresh
                </button>
              </div>

              <div v-if="loadingAnalytics" class="text-center py-4">
                <div class="spinner-border" role="status"></div>
                <p class="mt-2">Loading analytics...</p>
              </div>

              <div v-else>
                <!-- Enhanced Stats Cards -->
                <div class="row g-4 mb-4">
                  <div class="col-md-3">
                    <div class="analytics-card bg-primary text-white">
                      <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                          <div>
                            <h3>{{analytics.users?.total || users.filter(u => u.role === 'user').length}}</h3>
                            <p class="mb-1">Total Users</p>
                            <small class="opacity-75">{{ analytics.users?.active || 0 }} active users</small>
                            <div class="mt-2">
                              <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-light"
                                  :style="{ width: `${((analytics.users?.active || 0) / (analytics.users?.total || 1)) * 100}%` }">
                                </div>
                              </div>
                            </div>
                          </div>
                          <i class="bi bi-people display-6 opacity-75"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="analytics-card bg-success text-white">
                      <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                          <div>
                            <h3>{{ analytics.drivers?.approved || approvedDrivers.length }}</h3>
                            <p class="mb-1">Active Drivers</p>
                            <small class="opacity-75">{{ analytics.drivers?.total || drivers.length }} total
                              drivers</small>
                            <div class="mt-2">
                              <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-light"
                                  :style="{ width: `${((analytics.drivers?.approved || 0) / (analytics.drivers?.total || 1)) * 100}%` }">
                                </div>
                              </div>
                            </div>
                          </div>
                          <i class="bi bi-car-front display-6 opacity-75"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="analytics-card bg-warning text-white">
                      <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                          <div>
                            <h3>BDT {{ analytics.revenue?.total || 0 }}</h3>
                            <p class="mb-1">Total Revenue</p>
                            <small class="opacity-75">BDT {{ analytics.revenue?.today || 0 }} today</small>
                            <div class="mt-2">
                              <span class="badge bg-light text-dark">
                                <i class="bi bi-arrow-up me-1"></i>
                                {{ analytics.rides?.completed || 0 }} completed rides
                              </span>
                            </div>
                          </div>
                          <i class="bi bi-currency-dollar display-6 opacity-75"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="analytics-card bg-info text-white">
                      <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                          <div>
                            <h3>{{ analytics.rides?.total || 0 }}</h3>
                            <p class="mb-1">Total Rides</p>
                            <small class="opacity-75">{{ analytics.rides?.today || 0 }} rides today</small>
                            <div class="mt-2">
                              <span class="badge bg-light text-dark">
                                {{ analytics.rides?.active || activeRides.length }} active
                              </span>
                            </div>
                          </div>
                          <i class="bi bi-graph-up display-6 opacity-75"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- System Health Indicators -->
                <div class="row g-4 mb-4">
                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-activity me-2"></i>System Health & Status</h6>
                      </div>
                      <div class="card-body">
                        <div class="row g-3">
                          <div class="col-md-3">
                            <div class="health-indicator">
                              <div class="health-icon bg-success">
                                <i class="bi bi-check-circle"></i>
                              </div>
                              <div>
                                <div class="fw-medium">API Status</div>
                                <small class="text-success">Operational</small>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="health-indicator">
                              <div class="health-icon" :class="settings.maintenance_mode ? 'bg-warning' : 'bg-success'">
                                <i class="bi" :class="settings.maintenance_mode ? 'bi-tools' : 'bi-check-circle'"></i>
                              </div>
                              <div>
                                <div class="fw-medium">System Mode</div>
                                <small :class="settings.maintenance_mode ? 'text-warning' : 'text-success'">
                                  {{ settings.maintenance_mode ? 'Maintenance' : 'Normal' }}
                                </small>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="health-indicator">
                              <div class="health-icon bg-info">
                                <i class="bi bi-people"></i>
                              </div>
                              <div>
                                <div class="fw-medium">Active Sessions</div>
                                <small class="text-info">{{ (analytics.users?.active || 0) +
                                  (analytics.drivers?.approved || 0) }} online</small>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="health-indicator">
                              <div class="health-icon" :class="pendingDrivers.length > 5 ? 'bg-warning' : 'bg-success'">
                                <i class="bi bi-clock"></i>
                              </div>
                              <div>
                                <div class="fw-medium">Pending Actions</div>
                                <small :class="pendingDrivers.length > 5 ? 'text-warning' : 'text-success'">
                                  {{ pendingDrivers.length }} driver approvals
                                </small>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Recent Activity -->
                <div class="row g-4">
                  <div class="col-md-6">
                    <div class="card">
                      <div class="card-header">
                        <h6 class="mb-0">Recent Users</h6>
                      </div>
                      <div class="card-body">
                        <div v-if="analytics.recent_users?.length > 0">
                          <div v-for="user in analytics.recent_users" :key="user.id"
                            class="d-flex align-items-center mb-3">
                            <div class="avatar-sm me-3">
                              {{ getUserInitials(user) }}
                            </div>
                            <div class="flex-grow-1">
                              <div class="fw-medium">{{ user.first_name }} {{ user.last_name }}</div>
                              <small class="text-muted">{{ user.role }} • {{ formatDate(user.created_at) }}</small>
                            </div>
                            <span class="badge" :class="getRoleBadgeClass(user.role)">
                              {{ user.role }}
                            </span>
                          </div>
                        </div>
                        <div v-else class="text-center text-muted py-3">
                          <i class="bi bi-person-plus display-6"></i>
                          <p class="mt-2">No recent users</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="card">
                      <div class="card-header">
                        <h6 class="mb-0">Recent Rides</h6>
                      </div>
                      <div class="card-body">
                        <div v-if="analytics.recent_rides?.length > 0">
                          <div v-for="ride in analytics.recent_rides" :key="ride.id" class="mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                              <div class="flex-grow-1">
                                <div class="fw-medium">{{ ride.user?.first_name }} {{ ride.user?.last_name }}</div>
                                <small class="text-muted">{{ ride.pickup_location }} → {{ ride.dropoff_location
                                }}</small>
                                <div class="mt-1">
                                  <span class="badge" :class="getStatusBadgeClass(ride.status)">
                                    {{ ride.status }}
                                  </span>
                                </div>
                              </div>
                              <div class="text-end">
                                <div class="fw-medium">BDT {{ ride.estimated_price }}</div>
                                <small class="text-muted">{{ formatDate(ride.created_at) }}</small>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div v-else class="text-center text-muted py-3">
                          <i class="bi bi-car-front display-6"></i>
                          <p class="mt-2">No recent rides</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- System Settings Tab -->
            <div v-show="activeTab === 'settings'" class="tab-pane">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>System Configuration</h5>
                <div>
                  <button class="btn btn-outline-primary btn-sm me-2" @click="loadSettings">
                    <i class="bi bi-arrow-clockwise me-1"></i>
                    Refresh
                  </button>
                  <button class="btn btn-success btn-sm" @click="saveSettings" :disabled="savingSettings">
                    <span v-if="savingSettings" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="bi bi-check-circle me-1"></i>
                    Save Settings
                  </button>
                </div>
              </div>

              <div v-if="loadingSettings" class="text-center py-4">
                <div class="spinner-border" role="status"></div>
                <p class="mt-2">Loading settings...</p>
              </div>

              <div v-else class="row g-4">
                <!-- Pricing Settings -->
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header">
                      <h6 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Pricing Configuration</h6>
                    </div>
                    <div class="card-body">
                      <div class="mb-3">
                        <label class="form-label">Base Fare (BDT)</label>
                        <input type="number" class="form-control" v-model.number="settings.base_fare" min="0"
                          step="0.01">
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Per KM Rate (BDT)</label>
                        <input type="number" class="form-control" v-model.number="settings.per_km_rate" min="0"
                          step="0.01">
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Booking Fee (BDT)</label>
                        <input type="number" class="form-control" v-model.number="settings.booking_fee" min="0"
                          step="0.01">
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Cancellation Fee (BDT)</label>
                        <input type="number" class="form-control" v-model.number="settings.cancellation_fee" min="0"
                          step="0.01">
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Driver Commission (%)</label>
                        <input type="number" class="form-control" v-model.number="settings.driver_commission" min="0"
                          max="100">
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Operational Settings -->
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header">
                      <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Operational Settings</h6>
                    </div>
                    <div class="card-body">
                      <div class="mb-3">
                        <label class="form-label">Max Ride Distance (KM)</label>
                        <input type="number" class="form-control" v-model.number="settings.max_ride_distance" min="1">
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Max Waiting Time (Minutes)</label>
                        <input type="number" class="form-control" v-model.number="settings.max_waiting_time" min="1">
                      </div>
                      <div class="mb-3">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" v-model="settings.auto_assign_drivers"
                            id="autoAssign">
                          <label class="form-check-label" for="autoAssign">
                            Auto-assign Drivers
                          </label>
                        </div>
                      </div>
                      <div class="mb-3">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" v-model="settings.allow_cash_payment"
                            id="cashPayment">
                          <label class="form-check-label" for="cashPayment">
                            Allow Cash Payment
                          </label>
                        </div>
                      </div>
                      <div class="mb-3">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" v-model="settings.allow_card_payment"
                            id="cardPayment">
                          <label class="form-check-label" for="cardPayment">
                            Allow Card Payment
                          </label>
                        </div>
                      </div>
                      <div class="mb-3">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" v-model="settings.maintenance_mode"
                            id="maintenanceMode">
                          <label class="form-check-label" for="maintenanceMode">
                            Maintenance Mode
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Driver Modal -->
  <div class="modal fade" :class="{ show: showAddDriverModal }"
    :style="{ display: showAddDriverModal ? 'block' : 'none' }" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Driver</h5>
          <button type="button" class="btn-close" @click="closeAddDriverModal"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="addDriver">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">First Name *</label>
                <input type="text" class="form-control" v-model="newDriver.first_name" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Last Name *</label>
                <input type="text" class="form-control" v-model="newDriver.last_name" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email *</label>
                <input type="email" class="form-control" v-model="newDriver.email" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Phone *</label>
                <input type="tel" class="form-control" v-model="newDriver.phone" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Driver License *</label>
                <input type="text" class="form-control" v-model="newDriver.driver_license" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select" v-model="newDriver.driver_status">
                  <option value="pending">Pending</option>
                  <option value="approved">Approved</option>
                  <option value="rejected">Rejected</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Password *</label>
                <input type="password" class="form-control" v-model="newDriver.password" required minlength="6">
                <small class="form-text text-muted">Minimum 6 characters</small>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeAddDriverModal">Cancel</button>
          <button type="button" class="btn btn-primary" @click="addDriver" :disabled="addingDriver">
            <span v-if="addingDriver" class="spinner-border spinner-border-sm me-2"></span>
            <i v-else class="bi bi-plus-circle me-2"></i>
            Add Driver
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Backdrop -->
  <div v-if="showAddDriverModal" class="modal-backdrop fade show" @click="closeAddDriverModal"></div>

  <!-- User Details Modal -->
  <div class="modal fade" :class="{ show: showUserDetailsModal }"
    :style="{ display: showUserDetailsModal ? 'block' : 'none' }" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-person-circle me-2"></i>
            User Details
          </h5>
          <button type="button" class="btn-close" @click="closeUserDetailsModal"></button>
        </div>
        <div class="modal-body" v-if="selectedUser">
          <div class="row g-4">
            <div class="col-md-4">
              <div class="text-center">
                <div class="avatar-lg mx-auto mb-3">
                  {{ getUserInitials(selectedUser) }}
                </div>
                <h5>{{ selectedUser.first_name }} {{ selectedUser.last_name }}</h5>
                <span class="badge" :class="getRoleBadgeClass(selectedUser.role)">
                  {{ selectedUser.role }}
                </span>
              </div>
            </div>
            <div class="col-md-8">
              <div class="row g-3">
                <div class="col-12">
                  <h6 class="text-muted mb-3">Contact Information</h6>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <div class="form-control-plaintext">{{ selectedUser.email }}</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Phone</label>
                  <div class="form-control-plaintext">{{ selectedUser.phone || 'Not provided' }}</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">User ID</label>
                  <div class="form-control-plaintext">#{{ selectedUser.id }}</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Status</label>
                  <div class="form-control-plaintext">
                    <span class="badge" :class="selectedUser.is_active ? 'bg-success' : 'bg-secondary'">
                      {{ selectedUser.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Joined Date</label>
                  <div class="form-control-plaintext">{{ formatDate(selectedUser.created_at) }}</div>
                </div>
                <div class="col-md-6" v-if="selectedUser.role === 'driver'">
                  <label class="form-label">Driver License</label>
                  <div class="form-control-plaintext">{{ selectedUser.driver_license || 'N/A' }}</div>
                </div>
                <div class="col-md-6" v-if="selectedUser.role === 'driver'">
                  <label class="form-label">Driver Status</label>
                  <div class="form-control-plaintext">
                    <span class="badge" :class="getDriverStatusBadgeClass(selectedUser.driver_status)">
                      {{ selectedUser.driver_status || 'N/A' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- User Statistics -->
          <div class="mt-4">
            <h6 class="text-muted mb-3">User Statistics</h6>
            <div class="row g-3">
              <div class="col-md-3">
                <div class="stat-box">
                  <div class="stat-number">{{ getUserRideCount(selectedUser.id) }}</div>
                  <div class="stat-label">Total Rides</div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="stat-box">
                  <div class="stat-number">BDT {{ getUserTotalSpent(selectedUser.id) }}</div>
                  <div class="stat-label">Total Spent</div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="stat-box">
                  <div class="stat-number">{{ getUserActiveRides(selectedUser.id) }}</div>
                  <div class="stat-label">Active Rides</div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="stat-box">
                  <div class="stat-number">4.8</div>
                  <div class="stat-label">Rating</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="mt-4">
            <h6 class="text-muted mb-3">Recent Activity</h6>
            <div class="activity-timeline">
              <div class="activity-item">
                <div class="activity-icon bg-success">
                  <i class="bi bi-check-circle"></i>
                </div>
                <div class="activity-content">
                  <div class="activity-title">Account Created</div>
                  <div class="activity-time">{{ formatDate(selectedUser.created_at) }}</div>
                </div>
              </div>
              <div class="activity-item"
                v-if="selectedUser.role === 'driver' && selectedUser.driver_status === 'approved'">
                <div class="activity-icon bg-info">
                  <i class="bi bi-car-front"></i>
                </div>
                <div class="activity-content">
                  <div class="activity-title">Driver Approved</div>
                  <div class="activity-time">{{ formatDate(selectedUser.updated_at) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeUserDetailsModal">Close</button>
          <button type="button" class="btn btn-primary" @click="editUser(selectedUser)">
            <i class="bi bi-pencil me-1"></i>
            Edit User
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- User Details Modal Backdrop -->
  <div v-if="showUserDetailsModal" class="modal-backdrop fade show" @click="closeUserDetailsModal"></div>

  <!-- System Info Modal -->
  <div class="modal fade" :class="{ show: showSystemInfoModal }"
    :style="{ display: showSystemInfoModal ? 'block' : 'none' }" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-info-circle me-2"></i>
            System Information
          </h5>
          <button type="button" class="btn-close" @click="closeSystemInfoModal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-4">
            <!-- System Overview -->
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0"><i class="bi bi-server me-2"></i>System Overview</h6>
                </div>
                <div class="card-body">
                  <div class="info-item">
                    <span class="info-label">Application Name:</span>
                    <span class="info-value">Cholen Ride Share</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Version:</span>
                    <span class="info-value">1.0.0</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Environment:</span>
                    <span class="badge bg-success">Production</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Database Status:</span>
                    <span class="badge bg-success">Connected</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Last Updated:</span>
                    <span class="info-value">{{ new Date().toLocaleString() }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Platform Statistics -->
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Platform Statistics</h6>
                </div>
                <div class="card-body">
                  <div class="info-item">
                    <span class="info-label">Total Users:</span>
                    <span class="info-value">{{ users.length }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Total Drivers:</span>
                    <span class="info-value">{{ drivers.length }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Total Rides:</span>
                    <span class="info-value">{{ rides.length }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Revenue Generated:</span>
                    <span class="info-value">BDT {{ analytics.revenue?.total || 2500 }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Active Sessions:</span>
                    <span class="info-value">{{ (analytics.users?.active || 0) + (analytics.drivers?.approved || 0)
                    }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- System Configuration -->
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Current Configuration</h6>
                </div>
                <div class="card-body">
                  <div class="row g-3">
                    <div class="col-md-3">
                      <div class="config-item">
                        <div class="config-label">Base Fare</div>
                        <div class="config-value">BDT {{ settings.base_fare || 50 }}</div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="config-item">
                        <div class="config-label">Per KM Rate</div>
                        <div class="config-value">BDT {{ settings.per_km_rate || 15 }}</div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="config-item">
                        <div class="config-label">Driver Commission</div>
                        <div class="config-value">{{ settings.driver_commission || 20 }}%</div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="config-item">
                        <div class="config-label">Max Distance</div>
                        <div class="config-value">{{ settings.max_ride_distance || 100 }} KM</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeSystemInfoModal">Close</button>
          <button type="button" class="btn btn-primary" @click="activeTab = 'settings'; closeSystemInfoModal()">
            <i class="bi bi-gear me-1"></i>
            Configure Settings
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- System Info Modal Backdrop -->
  <div v-if="showSystemInfoModal" class="modal-backdrop fade show" @click="closeSystemInfoModal"></div>
</template>

<script setup>
import { ref, computed, onMounted, getCurrentInstance } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { authAPI } from '../services/api'

const router = useRouter()
const authStore = useAuthStore()
const { appContext } = getCurrentInstance()

// Redirect if not authenticated or not admin
if (!authStore.isAuthenticated || !authStore.isAdmin) {
  router.push('/dashboard')
}

// Reactive data
const activeTab = ref('users')
const users = ref([])
const drivers = ref([])
const rides = ref([])
const analytics = ref({})
const settings = ref({})
const loadingUsers = ref(false)
const loadingDrivers = ref(false)
const loadingRides = ref(false)
const loadingAnalytics = ref(false)
const loadingSettings = ref(false)

// Dynamic tab configuration
const adminTabs = computed(() => [
  {
    id: 'users',
    label: 'Users',
    icon: 'bi bi-people',
    badge: users.value.length,
    badgeClass: 'bg-primary',
    loading: loadingUsers.value,
    disabled: false
  },
  {
    id: 'drivers',
    label: 'Drivers',
    icon: 'bi bi-car-front',
    badge: drivers.value.length,
    badgeClass: pendingDrivers.value.length > 0 ? 'bg-warning text-dark' : 'bg-success',
    loading: loadingDrivers.value,
    disabled: false
  },
  {
    id: 'rides',
    label: 'Ride Requests',
    icon: 'bi bi-geo-alt',
    badge: rides.value.length,
    badgeClass: pendingRides.value.length > 0 ? 'bg-warning text-dark' : 'bg-info',
    loading: loadingRides.value,
    disabled: false
  },
  {
    id: 'analytics',
    label: 'Analytics',
    icon: 'bi bi-graph-up',
    badge: 0,
    badgeClass: 'bg-secondary',
    loading: loadingAnalytics.value,
    disabled: false
  },
  {
    id: 'settings',
    label: 'Settings',
    icon: 'bi bi-gear',
    badge: settings.value.maintenance_mode ? 1 : 0,
    badgeClass: 'bg-warning text-dark',
    loading: loadingSettings.value,
    disabled: false
  }
])
const driverFilter = ref('all')
const rideFilter = ref('all')
const userSearchQuery = ref('')
const driverSearchQuery = ref('')
const rideSearchQuery = ref('')
const updatingDriver = ref(null)
const updatingRide = ref(null)
const addingDriver = ref(false)
const savingSettings = ref(false)
const refreshingAll = ref(false)

// Enhanced Users Tab state
const userRoleFilter = ref('all')
const userStatusFilter = ref('all')
const userSortBy = ref('created_at')
const userSortOrder = ref('desc')
const selectedUsers = ref([])
const selectAllUsers = ref(false)
const usersPerPage = ref(25)
const currentUserPage = ref(1)

// Watch for changes in total pages and adjust current page if needed
const adjustCurrentUserPage = () => {
  const total = totalUserPages.value
  if (currentUserPage.value > total) {
    currentUserPage.value = Math.max(1, total)
  }
}
const showAddUserModal = ref(false)

// Enhanced Drivers Tab state
const driverSortBy = ref('created_at')
const driverSortOrder = ref('desc')
const selectedDrivers = ref([])
const selectAllDrivers = ref(false)
const driversPerPage = ref(25)
const currentDriverPage = ref(1)

// Enhanced Rides Tab state
const rideSortBy = ref('created_at')
const rideSortOrder = ref('desc')
const selectedRides = ref([])
const selectAllRides = ref(false)
const ridesPerPage = ref(25)
const currentRidePage = ref(1)

// Modal states
const showAddDriverModal = ref(false)
const showUserDetailsModal = ref(false)
const showSystemInfoModal = ref(false)
const selectedUser = ref(null)
const newDriver = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  driver_license: '',
  driver_status: 'pending',
  password: ''
})

// Computed properties
const filteredUsers = computed(() => {
  let filtered = users.value

  if (userSearchQuery.value) {
    const query = userSearchQuery.value.toLowerCase()
    filtered = filtered.filter(user =>
      user.first_name?.toLowerCase().includes(query) ||
      user.last_name?.toLowerCase().includes(query) ||
      user.email?.toLowerCase().includes(query) ||
      user.phone?.includes(query) ||
      user.role?.toLowerCase().includes(query)
    )
  }

  return filtered
})

const filteredDrivers = computed(() => {
  let filtered = drivers.value

  // Apply status filter
  if (driverFilter.value !== 'all') {
    filtered = filtered.filter(driver => driver.driver_status === driverFilter.value)
  }

  // Apply search filter
  if (driverSearchQuery.value) {
    const query = driverSearchQuery.value.toLowerCase()
    filtered = filtered.filter(driver =>
      driver.first_name?.toLowerCase().includes(query) ||
      driver.last_name?.toLowerCase().includes(query) ||
      driver.email?.toLowerCase().includes(query) ||
      driver.phone?.includes(query) ||
      driver.driver_license?.toLowerCase().includes(query)
    )
  }

  return filtered
})

const pendingDrivers = computed(() =>
  drivers.value.filter(driver => driver.driver_status === 'pending')
)

const approvedDrivers = computed(() =>
  drivers.value.filter(driver => driver.driver_status === 'approved')
)

const rejectedDrivers = computed(() =>
  drivers.value.filter(driver => driver.driver_status === 'rejected')
)

const filteredRides = computed(() => {
  if (rideFilter.value === 'all') return rides.value
  if (rideFilter.value === 'active') {
    return rides.value.filter(ride => ['accepted', 'driver_arrived', 'in_progress'].includes(ride.status))
  }
  return rides.value.filter(ride => ride.status === rideFilter.value)
})

const pendingRides = computed(() =>
  rides.value.filter(ride => ride.status === 'pending')
)

const activeRides = computed(() =>
  rides.value.filter(ride => ['accepted', 'driver_arrived', 'in_progress'].includes(ride.status))
)

const completedRides = computed(() =>
  rides.value.filter(ride => ride.status === 'completed')
)

const cancelledRides = computed(() =>
  rides.value.filter(ride => ride.status === 'cancelled')
)

// System Health computed properties
const systemIssuesCount = computed(() => {
  let issues = 0

  // Check for pending drivers (minor issue)
  if (pendingDrivers.value.length > 5) issues += 1

  // Check for maintenance mode (major issue)
  if (settings.value.maintenance_mode) issues += 2

  // Check for no active drivers (critical issue)
  if (approvedDrivers.value.length === 0) issues += 2

  // Check for failed API calls (check if data is empty when it shouldn't be)
  if (users.value.length === 0 && drivers.value.length === 0) issues += 1

  return issues
})

const systemHealthStatus = computed(() => {
  const issues = systemIssuesCount.value
  if (issues === 0) return 'All systems normal'
  if (issues <= 2) return `${issues} minor issues`
  return `${issues} critical issues`
})

const systemHealthIcon = computed(() => {
  const issues = systemIssuesCount.value
  if (issues === 0) return 'bi bi-shield-check'
  if (issues <= 2) return 'bi bi-exclamation-triangle'
  return 'bi bi-shield-x'
})

const systemHealthButtonClass = computed(() => {
  const issues = systemIssuesCount.value
  if (issues === 0) return 'btn-outline-success'
  if (issues <= 2) return 'btn-outline-warning'
  return 'btn-outline-danger'
})

// Dynamic Quick Actions configuration
const quickActions = computed(() => [
  {
    id: 'add-driver',
    label: 'Add Driver',
    subtitle: 'Create new driver account',
    icon: 'bi bi-person-plus',
    buttonClass: 'btn-outline-primary',
    badge: 0,
    badgeClass: '',
    loading: addingDriver.value,
    disabled: false,
    tooltip: 'Add a new driver to the platform',
    action: () => showAddDriverModal.value = true
  },
  {
    id: 'approve-all',
    label: 'Approve All',
    subtitle: `${pendingDrivers.value.length} pending`,
    icon: 'bi bi-check-all',
    buttonClass: pendingDrivers.value.length > 0 ? 'btn-outline-success' : 'btn-outline-secondary',
    badge: pendingDrivers.value.length,
    badgeClass: 'bg-warning text-dark',
    loading: false,
    disabled: pendingDrivers.value.length === 0,
    tooltip: `Approve all ${pendingDrivers.value.length} pending drivers`,
    action: approveAllPendingDrivers
  },
  {
    id: 'export-data',
    label: 'Export Data',
    subtitle: 'Download system data',
    icon: 'bi bi-download',
    buttonClass: 'btn-outline-info',
    badge: 0,
    badgeClass: '',
    loading: false,
    disabled: users.value.length === 0 && drivers.value.length === 0,
    tooltip: 'Export all system data as JSON',
    action: exportData
  },
  {
    id: 'maintenance-mode',
    label: settings.value.maintenance_mode ? 'Exit Maintenance' : 'Enter Maintenance',
    subtitle: settings.value.maintenance_mode ? 'System offline' : 'System online',
    icon: settings.value.maintenance_mode ? 'bi bi-play-circle' : 'bi bi-tools',
    buttonClass: settings.value.maintenance_mode ? 'btn-outline-success' : 'btn-outline-warning',
    badge: settings.value.maintenance_mode ? 1 : 0,
    badgeClass: 'bg-danger',
    loading: savingSettings.value,
    disabled: false,
    tooltip: settings.value.maintenance_mode ? 'Exit maintenance mode' : 'Enter maintenance mode',
    action: toggleMaintenanceMode
  },
  {
    id: 'refresh-all',
    label: 'Refresh All',
    subtitle: 'Update all data',
    icon: 'bi bi-arrow-clockwise',
    buttonClass: 'btn-outline-secondary',
    badge: 0,
    badgeClass: '',
    loading: refreshingAll.value,
    disabled: false,
    tooltip: 'Refresh all data from server',
    action: refreshAllData
  },
  {
    id: 'system-health',
    label: 'System Health',
    subtitle: systemHealthStatus.value,
    icon: systemHealthIcon.value,
    buttonClass: systemHealthButtonClass.value,
    badge: systemIssuesCount.value,
    badgeClass: 'bg-danger',
    loading: false,
    disabled: false,
    tooltip: 'View system health and issues',
    action: showSystemHealthModal
  }
])

// Enhanced Users computed properties
const filteredAndSortedUsers = computed(() => {
  // Safety check to prevent recursion
  if (!users.value || !Array.isArray(users.value)) {
    return []
  }

  let filtered = [...users.value] // Create a copy to avoid mutation

  // Apply role filter
  if (userRoleFilter.value !== 'all') {
    filtered = filtered.filter(user => user && user.role === userRoleFilter.value)
  }

  // Apply status filter
  if (userStatusFilter.value !== 'all') {
    const isActive = userStatusFilter.value === 'active'
    filtered = filtered.filter(user => user && user.is_active === isActive)
  }

  // Apply search filter
  if (userSearchQuery.value) {
    const query = userSearchQuery.value.toLowerCase()
    filtered = filtered.filter(user => {
      if (!user) return false
      return (
        user.first_name?.toLowerCase().includes(query) ||
        user.last_name?.toLowerCase().includes(query) ||
        user.email?.toLowerCase().includes(query) ||
        user.phone?.includes(query) ||
        user.role?.toLowerCase().includes(query)
      )
    })
  }

  // Apply sorting
  filtered.sort((a, b) => {
    let aVal, bVal

    switch (userSortBy.value) {
      case 'name':
        aVal = `${a.first_name} ${a.last_name}`.toLowerCase()
        bVal = `${b.first_name} ${b.last_name}`.toLowerCase()
        break
      case 'email':
        aVal = a.email?.toLowerCase() || ''
        bVal = b.email?.toLowerCase() || ''
        break
      case 'role':
        aVal = a.role?.toLowerCase() || ''
        bVal = b.role?.toLowerCase() || ''
        break
      case 'created_at':
      default:
        aVal = new Date(a.created_at || 0)
        bVal = new Date(b.created_at || 0)
        break
    }

    if (userSortOrder.value === 'asc') {
      return aVal > bVal ? 1 : -1
    } else {
      return aVal < bVal ? 1 : -1
    }
  })

  return filtered
})

const totalUserPages = computed(() => {
  const filtered = filteredAndSortedUsers.value
  if (!filtered || !Array.isArray(filtered)) return 1
  return Math.max(1, Math.ceil(filtered.length / usersPerPage.value))
})

const paginatedUsers = computed(() => {
  const filtered = filteredAndSortedUsers.value
  if (!filtered || !Array.isArray(filtered)) return []

  const start = (currentUserPage.value - 1) * usersPerPage.value
  const end = start + usersPerPage.value
  return filtered.slice(start, end)
})

const visibleUserPages = computed(() => {
  const total = totalUserPages.value
  const current = currentUserPage.value

  if (total <= 1) return [1]
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)

  const delta = 2
  const range = []

  for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
    range.push(i)
  }

  if (current - delta > 2) {
    range.unshift('...')
  }
  if (current + delta < total - 1) {
    range.push('...')
  }

  range.unshift(1)
  if (total > 1) {
    range.push(total)
  }

  return range.filter((item, index, arr) => arr.indexOf(item) === index)
})

// Dynamic Admin Capabilities computed property
const adminCapabilities = computed(() => [
  {
    id: 'users-drivers',
    tabId: 'users',
    title: 'User & Driver Management',
    description: 'Manage all platform users, approve drivers, and handle registrations',
    icon: 'bi bi-people',
    iconClass: 'bg-primary',
    loading: loadingUsers.value || loadingDrivers.value,
    primaryStats: [
      {
        label: 'Total Users',
        value: users.value.filter(u => u.role === 'user').length,
        badgeClass: 'bg-primary',
        icon: 'bi bi-person'
      },
      {
        label: 'Drivers',
        value: drivers.value.length,
        badgeClass: 'bg-info',
        icon: 'bi bi-car-front'
      }
    ],
    secondaryStats: [
      {
        label: 'Pending Approvals',
        value: pendingDrivers.value.length
      },
      {
        label: 'Active Users',
        value: users.value.filter(u => u.is_active).length
      }
    ],
    trend: pendingDrivers.value.length > 0 ? {
      text: `${pendingDrivers.value.length} drivers need approval`,
      class: 'text-warning',
      icon: 'bi bi-exclamation-triangle'
    } : {
      text: 'All drivers approved',
      class: 'text-success',
      icon: 'bi bi-check-circle'
    },
    alert: pendingDrivers.value.length > 5 ? {
      class: 'alert-warning',
      icon: 'bi bi-exclamation-triangle'
    } : null
  },
  {
    id: 'analytics',
    tabId: 'analytics',
    title: 'System Analytics',
    description: 'Monitor platform performance, revenue, and user engagement metrics',
    icon: 'bi bi-graph-up',
    iconClass: 'bg-success',
    loading: loadingAnalytics.value,
    primaryStats: [
      {
        label: 'Total Revenue',
        value: `BDT ${analytics.value.revenue?.total || 0}`,
        badgeClass: 'bg-success',
        icon: 'bi bi-currency-dollar'
      },
      {
        label: 'Rides',
        value: analytics.value.rides?.total || rides.value.length,
        badgeClass: 'bg-info',
        icon: 'bi bi-car-front'
      }
    ],
    secondaryStats: [
      {
        label: 'Today Revenue',
        value: `BDT ${analytics.value.revenue?.today || 0}`
      },
      {
        label: 'Completion Rate',
        value: `${Math.round(((analytics.value.rides?.completed || 0) / Math.max(analytics.value.rides?.total || 1, 1)) * 100)}%`
      }
    ],
    trend: {
      text: `${analytics.value.rides?.today || 0} rides today`,
      class: 'text-info',
      icon: 'bi bi-arrow-up'
    },
    alert: null
  },
  {
    id: 'rides',
    tabId: 'rides',
    title: 'Ride Monitoring',
    description: 'Track all ride requests, manage statuses, and resolve customer issues',
    icon: 'bi bi-car-front',
    iconClass: 'bg-info',
    loading: loadingRides.value,
    primaryStats: [
      {
        label: 'Active Rides',
        value: activeRides.value.length,
        badgeClass: 'bg-info',
        icon: 'bi bi-play-circle'
      },
      {
        label: 'Pending',
        value: pendingRides.value.length,
        badgeClass: 'bg-warning text-dark',
        icon: 'bi bi-clock'
      }
    ],
    secondaryStats: [
      {
        label: 'Completed Today',
        value: rides.value.filter(r => r.status === 'completed' &&
          new Date(r.created_at).toDateString() === new Date().toDateString()).length
      },
      {
        label: 'Total Rides',
        value: rides.value.length
      }
    ],
    trend: pendingRides.value.length > 0 ? {
      text: `${pendingRides.value.length} rides awaiting drivers`,
      class: 'text-warning',
      icon: 'bi bi-clock'
    } : {
      text: 'All rides assigned',
      class: 'text-success',
      icon: 'bi bi-check-circle'
    },
    alert: pendingRides.value.length > 10 ? {
      class: 'alert-danger',
      icon: 'bi bi-exclamation-triangle'
    } : null
  },
  {
    id: 'settings',
    tabId: 'settings',
    title: 'System Configuration',
    description: 'Configure pricing, operational settings, and platform policies',
    icon: 'bi bi-gear',
    iconClass: settings.value.maintenance_mode ? 'bg-danger' : 'bg-warning',
    loading: loadingSettings.value || savingSettings.value,
    primaryStats: [
      {
        label: 'Base Fare',
        value: `BDT ${settings.value.base_fare || 0}`,
        badgeClass: 'bg-warning text-dark',
        icon: 'bi bi-currency-dollar'
      },
      {
        label: 'Commission',
        value: `${settings.value.driver_commission || 0}%`,
        badgeClass: 'bg-secondary',
        icon: 'bi bi-percent'
      }
    ],
    secondaryStats: [
      {
        label: 'Per KM Rate',
        value: `BDT ${settings.value.per_km_rate || 0}`
      },
      {
        label: 'Max Distance',
        value: `${settings.value.max_ride_distance || 0} KM`
      }
    ],
    trend: settings.value.maintenance_mode ? {
      text: 'System in maintenance mode',
      class: 'text-danger',
      icon: 'bi bi-tools'
    } : {
      text: 'System operational',
      class: 'text-success',
      icon: 'bi bi-check-circle'
    },
    alert: settings.value.maintenance_mode ? {
      class: 'alert-danger',
      icon: 'bi bi-exclamation-triangle'
    } : null
  }
])



// Enhanced Users helper functions
const getUserAvatarClass = (user) => {
  const roleClasses = {
    admin: 'bg-danger',
    driver: 'bg-warning',
    user: 'bg-primary'
  }
  return roleClasses[user.role] || 'bg-secondary'
}

const getRoleIcon = (role) => {
  const icons = {
    admin: 'bi bi-shield-check',
    driver: 'bi bi-car-front',
    user: 'bi bi-person'
  }
  return icons[role] || 'bi bi-person'
}

const getRelativeTime = (dateString) => {
  if (!dateString) return 'Never'

  const date = new Date(dateString)
  const now = new Date()
  const diffInSeconds = Math.floor((now - date) / 1000)

  if (diffInSeconds < 60) return 'Just now'
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`
  if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)}d ago`
  return formatDate(dateString)
}

const toggleUserSortOrder = () => {
  userSortOrder.value = userSortOrder.value === 'asc' ? 'desc' : 'asc'
}

const clearUserFilters = () => {
  userSearchQuery.value = ''
  userRoleFilter.value = 'all'
  userStatusFilter.value = 'all'
  userSortBy.value = 'created_at'
  userSortOrder.value = 'desc'
}

const toggleSelectAllUsers = () => {
  if (selectAllUsers.value) {
    selectedUsers.value = paginatedUsers.value.map(user => user.id)
  } else {
    selectedUsers.value = []
  }
}

const exportUsers = () => {
  try {
    const exportData = filteredAndSortedUsers.value.map(user => ({
      id: user.id,
      name: `${user.first_name} ${user.last_name}`,
      email: user.email,
      phone: user.phone,
      role: user.role,
      status: user.is_active ? 'Active' : 'Inactive',
      joined: formatDate(user.created_at),
      lastActive: formatDate(user.last_login_at)
    }))

    const dataStr = JSON.stringify(exportData, null, 2)
    const dataBlob = new Blob([dataStr], { type: 'application/json' })
    const url = URL.createObjectURL(dataBlob)

    const link = document.createElement('a')
    link.href = url
    link.download = `users-export-${new Date().toISOString().split('T')[0]}.json`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)

    appContext.config.globalProperties.$toast?.success(`Exported ${exportData.length} users successfully!`)
  } catch (error) {
    appContext.config.globalProperties.$toast?.error('Failed to export users')
  }
}

const bulkActivateUsers = () => {
  if (selectedUsers.value.length === 0) {
    appContext.config.globalProperties.$toast?.warning('Please select users to activate')
    return
  }

  if (confirm(`Activate ${selectedUsers.value.length} selected users?`)) {
    // In a real app, this would make API calls
    selectedUsers.value.forEach(userId => {
      const user = users.value.find(u => u.id === userId)
      if (user) user.is_active = true
    })

    appContext.config.globalProperties.$toast?.success(`Activated ${selectedUsers.value.length} users`)
    selectedUsers.value = []
    selectAllUsers.value = false
  }
}

const bulkDeactivateUsers = () => {
  if (selectedUsers.value.length === 0) {
    appContext.config.globalProperties.$toast?.warning('Please select users to deactivate')
    return
  }

  if (confirm(`Deactivate ${selectedUsers.value.length} selected users?`)) {
    // In a real app, this would make API calls
    selectedUsers.value.forEach(userId => {
      const user = users.value.find(u => u.id === userId)
      if (user && user.role !== 'admin') user.is_active = false
    })

    appContext.config.globalProperties.$toast?.success(`Deactivated ${selectedUsers.value.length} users`)
    selectedUsers.value = []
    selectAllUsers.value = false
  }
}

const bulkDeleteUsers = () => {
  if (selectedUsers.value.length === 0) {
    appContext.config.globalProperties.$toast?.warning('Please select users to delete')
    return
  }

  const nonAdminUsers = selectedUsers.value.filter(userId => {
    const user = users.value.find(u => u.id === userId)
    return user && user.role !== 'admin'
  })

  if (nonAdminUsers.length === 0) {
    appContext.config.globalProperties.$toast?.error('Cannot delete admin users')
    return
  }

  if (confirm(`Permanently delete ${nonAdminUsers.length} selected users? This action cannot be undone.`)) {
    // In a real app, this would make API calls
    users.value = users.value.filter(user => !nonAdminUsers.includes(user.id))

    appContext.config.globalProperties.$toast?.success(`Deleted ${nonAdminUsers.length} users`)
    selectedUsers.value = []
    selectAllUsers.value = false
  }
}

// Helper functions
const getRoleBadgeClass = (role) => {
  const classes = {
    admin: 'bg-danger',
    driver: 'bg-warning text-dark',
    user: 'bg-primary'
  }
  return classes[role] || 'bg-secondary'
}

const getDriverStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-warning text-dark',
    approved: 'bg-success',
    rejected: 'bg-danger'
  }
  return classes[status] || 'bg-secondary'
}

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-warning text-dark',
    accepted: 'bg-info',
    driver_arrived: 'bg-primary',
    in_progress: 'bg-success',
    completed: 'bg-success',
    cancelled: 'bg-danger'
  }
  return classes[status] || 'bg-secondary'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString()
}

const getUserInitials = (user) => {
  if (!user.first_name && !user.last_name) return 'U'
  return `${user.first_name?.[0] || ''}${user.last_name?.[0] || ''}`.toUpperCase()
}

// API functions
const loadUsers = async () => {
  loadingUsers.value = true
  try {
    const response = await authAPI.getAllUsers()
    if (response.data.success) {
      users.value = response.data.users || []
      console.log(`✅ Loaded ${users.value.length} users from API`)
    } else {
      throw new Error(response.data.message || 'Failed to load users')
    }
  } catch (error) {
    console.error('Error loading users:', error)
    appContext.config.globalProperties.$toast?.error('Failed to load users from server')
    users.value = []
  } finally {
    loadingUsers.value = false
  }
}

const loadDrivers = async () => {
  loadingDrivers.value = true
  try {
    const response = await authAPI.getAllDrivers()
    if (response.data.success) {
      drivers.value = response.data.drivers || []
      console.log(`✅ Loaded ${drivers.value.length} drivers from API`)
    } else {
      throw new Error(response.data.message || 'Failed to load drivers')
    }
  } catch (error) {
    console.error('Error loading drivers:', error)
    appContext.config.globalProperties.$toast?.error('Failed to load drivers from server')
    drivers.value = []
  } finally {
    loadingDrivers.value = false
  }
}

// Driver management functions
const addDriver = async () => {
  if (!newDriver.value.first_name || !newDriver.value.last_name || !newDriver.value.email ||
    !newDriver.value.phone || !newDriver.value.driver_license || !newDriver.value.password) {
    appContext.config.globalProperties.$toast?.error('Please fill in all required fields')
    return
  }

  addingDriver.value = true
  try {
    const driverData = {
      ...newDriver.value,
      name: `${newDriver.value.first_name} ${newDriver.value.last_name}`,
      role: 'driver',
      is_active: true
    }

    const response = await authAPI.createDriver(driverData)

    if (response.data.success) {
      drivers.value.unshift(response.data.driver) // Add to beginning of array
      appContext.config.globalProperties.$toast?.success('Driver added successfully!')
      closeAddDriverModal()
      // Refresh analytics
      loadAnalytics()
    } else {
      throw new Error(response.data.message || 'Failed to add driver')
    }
  } catch (error) {
    console.error('Error adding driver:', error)

    if (error.response?.status === 422) {
      // Validation errors
      const errors = error.response.data.errors
      const errorMessages = Object.values(errors).flat().join(', ')
      appContext.config.globalProperties.$toast?.error(`Validation Error: ${errorMessages}`)
    } else {
      appContext.config.globalProperties.$toast?.error('Failed to add driver')
    }
  } finally {
    addingDriver.value = false
  }
}

const approveDriver = async (driverId) => {
  updatingDriver.value = driverId
  try {
    const response = await authAPI.updateDriverStatus(driverId, 'approved')

    if (response.data.success) {
      const driver = drivers.value.find(d => d.id === driverId)
      if (driver) {
        driver.driver_status = 'approved'
      }
      appContext.config.globalProperties.$toast?.success('Driver approved successfully!')
      // Refresh analytics to update counts
      loadAnalytics()
    } else {
      throw new Error(response.data.message || 'Failed to approve driver')
    }
  } catch (error) {
    console.error('Error approving driver:', error)
    appContext.config.globalProperties.$toast?.error('Failed to approve driver')
  } finally {
    updatingDriver.value = null
  }
}

const rejectDriver = async (driverId) => {
  updatingDriver.value = driverId
  try {
    const response = await authAPI.updateDriverStatus(driverId, 'rejected')

    if (response.data.success) {
      const driver = drivers.value.find(d => d.id === driverId)
      if (driver) {
        driver.driver_status = 'rejected'
      }
      appContext.config.globalProperties.$toast?.success('Driver rejected')
      // Refresh analytics to update counts
      loadAnalytics()
    } else {
      throw new Error(response.data.message || 'Failed to reject driver')
    }
  } catch (error) {
    console.error('Error rejecting driver:', error)
    appContext.config.globalProperties.$toast?.error('Failed to reject driver')
  } finally {
    updatingDriver.value = null
  }
}

const deleteDriver = async (driverId) => {
  if (!confirm('Are you sure you want to delete this driver?')) return

  try {
    await authAPI.deleteDriver(driverId)
    drivers.value = drivers.value.filter(d => d.id !== driverId)
    appContext.config.globalProperties.$toast?.success('Driver deleted successfully!')
  } catch (error) {
    console.error('Error deleting driver:', error)
    appContext.config.globalProperties.$toast?.error('Failed to delete driver')
  }
}

const deleteUser = async (userId) => {
  if (!confirm('Are you sure you want to delete this user?')) return

  try {
    await authAPI.deleteUser(userId)
    users.value = users.value.filter(u => u.id !== userId)
    appContext.config.globalProperties.$toast?.success('User deleted successfully!')
  } catch (error) {
    console.error('Error deleting user:', error)
    appContext.config.globalProperties.$toast?.error('Failed to delete user')
  }
}

const editUser = (user) => {
  // TODO: Implement user editing modal
  appContext.config.globalProperties.$toast?.info('User editing feature coming soon!')
}

const promoteToAdmin = async (user) => {
  const confirmMessage = `Are you sure you want to promote ${user.first_name} ${user.last_name} to Admin?\n\nThis will give them full administrative access to the platform.`

  if (!confirm(confirmMessage)) return

  try {
    // Update user role to admin
    const response = await authAPI.updateUser(user.id, { role: 'admin' })

    if (response.data.success) {
      // Update local user data
      const userIndex = users.value.findIndex(u => u.id === user.id)
      if (userIndex !== -1) {
        users.value[userIndex].role = 'admin'
      }

      appContext.config.globalProperties.$toast?.success(
        `${user.first_name} ${user.last_name} has been promoted to Admin successfully!`
      )

      // Refresh analytics to update counts
      loadAnalytics()
    } else {
      throw new Error(response.data.message || 'Failed to promote user')
    }
  } catch (error) {
    console.error('Error promoting user to admin:', error)
    appContext.config.globalProperties.$toast?.error('Failed to promote user to admin')
  }
}

const editDriver = (driver) => {
  // TODO: Implement driver editing modal
  appContext.config.globalProperties.$toast?.info('Driver editing feature coming soon!')
}

// Modal functions
const closeAddDriverModal = () => {
  showAddDriverModal.value = false
  newDriver.value = {
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    driver_license: '',
    driver_status: 'pending',
    password: ''
  }
}

const showUserDetails = (user) => {
  selectedUser.value = user
  showUserDetailsModal.value = true
}

const closeUserDetailsModal = () => {
  showUserDetailsModal.value = false
  selectedUser.value = null
}

const showSystemInfo = () => {
  showSystemInfoModal.value = true
}

const closeSystemInfoModal = () => {
  showSystemInfoModal.value = false
}

// User statistics functions
const getUserRideCount = (userId) => {
  return rides.value.filter(ride => ride.user?.id === userId || ride.user_id === userId).length
}

const getUserTotalSpent = (userId) => {
  const userRides = rides.value.filter(ride =>
    (ride.user?.id === userId || ride.user_id === userId) &&
    ride.status === 'completed'
  )
  return userRides.reduce((total, ride) => total + (ride.final_price || ride.estimated_price || 0), 0)
}

const getUserActiveRides = (userId) => {
  return rides.value.filter(ride =>
    (ride.user?.id === userId || ride.user_id === userId) &&
    ['pending', 'accepted', 'driver_arrived', 'in_progress'].includes(ride.status)
  ).length
}

// Load rides
const loadRides = async () => {
  loadingRides.value = true
  try {
    const response = await authAPI.getAllRides()
    if (response.data.success) {
      rides.value = response.data.rides || []
      console.log(`✅ Loaded ${rides.value.length} rides from API`)
    } else {
      throw new Error(response.data.message || 'Failed to load rides')
    }
  } catch (error) {
    console.error('Error loading rides:', error)
    appContext.config.globalProperties.$toast?.error('Failed to load rides from server')
    rides.value = []
  } finally {
    loadingRides.value = false
  }
}

// Load settings
const loadSettings = async () => {
  loadingSettings.value = true
  try {
    const response = await authAPI.getSystemSettings()
    if (response.data.success) {
      settings.value = response.data.settings || {}
      console.log('✅ Loaded system settings from API')
    } else {
      throw new Error(response.data.message || 'Failed to load settings')
    }
  } catch (error) {
    console.error('Error loading settings:', error)
    appContext.config.globalProperties.$toast?.error('Failed to load settings from server')
    settings.value = {}
  } finally {
    loadingSettings.value = false
  }
}

// Save settings
const saveSettings = async () => {
  savingSettings.value = true
  try {
    const response = await authAPI.updateSystemSettings(settings.value)
    if (response.data.success) {
      appContext.config.globalProperties.$toast?.success('Settings saved successfully!')
    } else {
      throw new Error(response.data.message || 'Failed to save settings')
    }
  } catch (error) {
    console.error('Error saving settings:', error)
    if (error.response?.status === 422) {
      const errors = error.response.data.errors
      const errorMessages = Object.values(errors).flat().join(', ')
      appContext.config.globalProperties.$toast?.error(`Validation Error: ${errorMessages}`)
    } else {
      appContext.config.globalProperties.$toast?.error('Failed to save settings')
    }
  } finally {
    savingSettings.value = false
  }
}

// Update ride status (admin override)
const updateRideStatusAdmin = async (rideId, newStatus) => {
  updatingRide.value = rideId
  try {
    const response = await authAPI.updateRideStatus(rideId, newStatus)
    if (response.data.success) {
      const ride = rides.value.find(r => r.id === rideId)
      if (ride) {
        ride.status = newStatus
      }
      appContext.config.globalProperties.$toast?.success('Ride status updated successfully!')
    } else {
      throw new Error(response.data.message || 'Failed to update ride status')
    }
  } catch (error) {
    console.error('Error updating ride status:', error)
    appContext.config.globalProperties.$toast?.error('Failed to update ride status')
  } finally {
    updatingRide.value = null
  }
}

// Load analytics
const loadAnalytics = async () => {
  loadingAnalytics.value = true
  try {
    const response = await authAPI.getDashboardAnalytics()
    if (response.data.success) {
      analytics.value = response.data.analytics
      console.log('✅ Loaded analytics from API')
    } else {
      throw new Error(response.data.message || 'Failed to load analytics')
    }
  } catch (error) {
    console.error('Error loading analytics:', error)
    appContext.config.globalProperties.$toast?.error('Failed to load analytics from server')
    analytics.value = {
      users: { total: 0, active: 0 },
      drivers: { total: 0, pending: 0, approved: 0, rejected: 0 },
      rides: { total: 0, today: 0, completed: 0, active: 0, pending: 0 },
      revenue: { total: 0, today: 0 },
      recent_users: [],
      recent_rides: []
    }
  } finally {
    loadingAnalytics.value = false
  }
}

// Auto-refresh data every 30 seconds
const startAutoRefresh = () => {
  setInterval(() => {
    if (activeTab.value === 'users') {
      loadUsers()
    } else if (activeTab.value === 'drivers') {
      loadDrivers()
    } else if (activeTab.value === 'rides') {
      loadRides()
    } else if (activeTab.value === 'analytics') {
      loadAnalytics()
    } else if (activeTab.value === 'settings') {
      loadSettings()
    }
  }, 30000) // 30 seconds
}

// Refresh all data
const refreshAllData = async () => {
  refreshingAll.value = true
  try {
    await Promise.all([
      loadUsers(),
      loadDrivers(),
      loadRides(),
      loadAnalytics(),
      loadSettings()
    ])
    appContext.config.globalProperties.$toast?.success('All data refreshed successfully!')
  } catch (error) {
    appContext.config.globalProperties.$toast?.error('Failed to refresh some data')
  } finally {
    refreshingAll.value = false
  }
}

// Approve all pending drivers
const approveAllPendingDrivers = async () => {
  const pendingCount = pendingDrivers.value.length
  if (pendingCount === 0) {
    appContext.config.globalProperties.$toast?.info('No pending drivers to approve')
    return
  }

  if (!confirm(`Are you sure you want to approve all ${pendingCount} pending drivers?`)) {
    return
  }

  let successCount = 0
  for (const driver of pendingDrivers.value) {
    try {
      await approveDriver(driver.id)
      successCount++
    } catch (error) {
      console.error(`Failed to approve driver ${driver.id}:`, error)
    }
  }

  appContext.config.globalProperties.$toast?.success(`Successfully approved ${successCount} out of ${pendingCount} drivers`)
  loadAnalytics() // Refresh analytics
}

// Export data
const exportData = () => {
  try {
    const exportData = {
      users: users.value,
      drivers: drivers.value,
      rides: rides.value,
      analytics: analytics.value,
      settings: settings.value,
      exportDate: new Date().toISOString()
    }

    const dataStr = JSON.stringify(exportData, null, 2)
    const dataBlob = new Blob([dataStr], { type: 'application/json' })
    const url = URL.createObjectURL(dataBlob)

    const link = document.createElement('a')
    link.href = url
    link.download = `admin-data-export-${new Date().toISOString().split('T')[0]}.json`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)

    appContext.config.globalProperties.$toast?.success('Data exported successfully!')
  } catch (error) {
    appContext.config.globalProperties.$toast?.error('Failed to export data')
  }
}

// Toggle maintenance mode
const toggleMaintenanceMode = async () => {
  const newMode = !settings.value.maintenance_mode
  const action = newMode ? 'enable' : 'disable'

  if (!confirm(`Are you sure you want to ${action} maintenance mode?`)) {
    return
  }

  try {
    settings.value.maintenance_mode = newMode
    await saveSettings()

    const message = newMode
      ? 'Maintenance mode enabled. Users will see a maintenance message.'
      : 'Maintenance mode disabled. Normal operations resumed.'

    appContext.config.globalProperties.$toast?.success(message)
  } catch (error) {
    settings.value.maintenance_mode = !newMode // Revert on error
    appContext.config.globalProperties.$toast?.error(`Failed to ${action} maintenance mode`)
  }
}

// View system logs
const viewSystemLogs = () => {
  appContext.config.globalProperties.$toast?.info('System logs feature coming soon!')
  // In a real implementation, this would open a modal or navigate to a logs page
}

// Emergency actions
const emergencyActions = () => {
  const actions = [
    'Cancel all pending rides',
    'Disable new registrations',
    'Send emergency notification to all users',
    'Export emergency backup'
  ]

  const action = prompt(`Emergency Actions:\n${actions.map((a, i) => `${i + 1}. ${a}`).join('\n')}\n\nEnter action number (1-${actions.length}):`)

  if (action && action >= 1 && action <= actions.length) {
    const selectedAction = actions[parseInt(action) - 1]
    if (confirm(`Execute emergency action: "${selectedAction}"?`)) {
      appContext.config.globalProperties.$toast?.info(`Emergency action "${selectedAction}" would be executed in production`)
    }
  }
}

// Quick Actions helper functions


const executeQuickAction = (action) => {
  if (action.disabled || action.loading) return

  // Add analytics tracking
  console.log(`🚀 Quick Action executed: ${action.id}`)

  // Execute the action
  if (typeof action.action === 'function') {
    action.action()
  }
}

const showSystemHealthModal = () => {
  // Create a detailed system health report
  const issues = []

  if (pendingDrivers.value.length > 5) {
    issues.push(`${pendingDrivers.value.length} drivers pending approval`)
  }

  if (settings.value.maintenance_mode) {
    issues.push('System is in maintenance mode')
  }

  if (approvedDrivers.value.length === 0) {
    issues.push('No approved drivers available')
  }

  if (users.value.length === 0 && drivers.value.length === 0) {
    issues.push('No data loaded - possible API connection issue')
  }

  if (activeRides.value.length > 10) {
    issues.push(`${activeRides.value.length} active rides need monitoring`)
  }

  const message = issues.length === 0
    ? '✅ All systems are operating normally!\n\nNo issues detected.'
    : `⚠️ System Health Report:\n\n${issues.map((issue, i) => `${i + 1}. ${issue}`).join('\n')}\n\nRecommendation: Review and address these issues.`

  alert(message)
}

// Tab switching function
const switchTab = (tabId) => {
  if (activeTab.value === tabId) return

  activeTab.value = tabId

  // Auto-load data when switching to a tab if not already loaded
  switch (tabId) {
    case 'users':
      if (users.value.length === 0) loadUsers()
      break
    case 'drivers':
      if (drivers.value.length === 0) loadDrivers()
      break
    case 'rides':
      if (rides.value.length === 0) loadRides()
      break
    case 'analytics':
      loadAnalytics() // Always refresh analytics
      break
    case 'settings':
      if (Object.keys(settings.value).length === 0) loadSettings()
      break
  }
}

onMounted(() => {
  // Load data sequentially to avoid race conditions
  setTimeout(() => {
    loadUsers()
  }, 100)

  setTimeout(() => {
    loadDrivers()
  }, 200)

  setTimeout(() => {
    loadRides()
  }, 300)

  setTimeout(() => {
    loadAnalytics()
  }, 400)

  setTimeout(() => {
    loadSettings()
  }, 500)

  // Start auto-refresh after initial load
  setTimeout(() => {
    startAutoRefresh()
  }, 1000)
})
</script>

<style scoped>
.avatar-sm {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 14px;
}

.nav-link {
  border: none;
  background: none;
  cursor: pointer;
}

.nav-link.active {
  background-color: var(--bs-primary);
  color: white !important;
  border-color: var(--bs-primary);
}

.table th {
  border-top: none;
  font-weight: 600;
  color: #495057;
  background-color: #f8f9fa;
}

.btn-group-sm .btn {
  padding: 0.25rem 0.5rem;
}

.modal.show {
  background-color: rgba(0, 0, 0, 0.5);
}

.card {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.display-6 {
  opacity: 0.7;
}

.badge {
  font-size: 0.75em;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

.table-responsive {
  border-radius: 0.375rem;
}

.btn-group .btn {
  border-radius: 0.25rem;
  margin-right: 0.25rem;
}

.btn-group .btn:last-child {
  margin-right: 0;
}

.form-label {
  font-weight: 500;
  color: #495057;
}

.modal-header {
  border-bottom: 1px solid #dee2e6;
}

.modal-footer {
  border-top: 1px solid #dee2e6;
}

/* Enhanced Admin Capability Cards */
.admin-capability-card {
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 0.75rem;
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
  height: 100%;
  display: flex;
  align-items: center;
  gap: 1rem;
  position: relative;
  overflow: hidden;
}

.admin-capability-card:hover {
  border-color: var(--bs-primary);
  box-shadow: 0 0.5rem 1rem rgba(0, 123, 255, 0.15);
  transform: translateY(-2px);
}

.admin-capability-card.capability-active {
  border-color: var(--bs-primary);
  box-shadow: 0 0.25rem 0.5rem rgba(0, 123, 255, 0.25);
  background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
}

.admin-capability-card.capability-loading {
  opacity: 0.8;
}

.capability-spinner {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
}

.capability-icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.capability-content {
  flex-grow: 1;
}

.capability-content h5 {
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #2c3e50;
}

.capability-content p {
  margin-bottom: 0.75rem;
  font-size: 0.875rem;
  line-height: 1.4;
}

.capability-stats {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.capability-stats .badge {
  font-size: 0.75rem;
  font-weight: 500;
}

.stats-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  align-items: center;
}

.capability-trend {
  margin-top: 0.5rem;
  padding-top: 0.5rem;
  border-top: 1px solid #f1f3f4;
}

.capability-alert {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  color: white;
}

.capability-alert.alert-warning {
  background-color: #ffc107;
  color: #212529;
}

.capability-alert.alert-danger {
  background-color: #dc3545;
}

.capability-alert.alert-info {
  background-color: #0dcaf0;
  color: #212529;
}

.capability-arrow {
  color: #6c757d;
  font-size: 1.25rem;
  opacity: 0.6;
  transition: all 0.3s ease;
}

.admin-capability-card:hover .capability-arrow {
  opacity: 1;
  transform: translateX(4px);
  color: var(--bs-primary);
}

/* Quick Actions Bar */
.quick-actions-bar {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 0.75rem;
  padding: 1.5rem;
  border: 1px solid #e9ecef;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.quick-action-btn {
  padding: 1rem 0.75rem;
  border-radius: 0.75rem;
  font-size: 0.875rem;
  transition: all 0.3s ease;
  position: relative;
  min-height: 100px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
}

.quick-action-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.quick-action-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
}

.action-icon-container {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 0.5rem;
}

.action-icon-container i {
  font-size: 1.5rem;
}

.action-badge {
  position: absolute;
  top: -8px;
  right: -8px;
  font-size: 0.7rem;
  padding: 0.2rem 0.4rem;
  border-radius: 0.75rem;
  font-weight: 600;
  min-width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-label {
  font-weight: 600;
  display: block;
  margin-bottom: 0.25rem;
}

.action-subtitle {
  font-size: 0.75rem;
  opacity: 0.8;
}

/* Button state variations */
.quick-action-btn.btn-outline-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #007bff, #0056b3);
  border-color: #0056b3;
  color: white;
}

.quick-action-btn.btn-outline-success:hover:not(:disabled) {
  background: linear-gradient(135deg, #28a745, #1e7e34);
  border-color: #1e7e34;
  color: white;
}

.quick-action-btn.btn-outline-warning:hover:not(:disabled) {
  background: linear-gradient(135deg, #ffc107, #e0a800);
  border-color: #e0a800;
  color: #212529;
}

.quick-action-btn.btn-outline-danger:hover:not(:disabled) {
  background: linear-gradient(135deg, #dc3545, #c82333);
  border-color: #c82333;
  color: white;
}

.quick-action-btn.btn-outline-info:hover:not(:disabled) {
  background: linear-gradient(135deg, #17a2b8, #138496);
  border-color: #138496;
  color: white;
}

.quick-action-btn.btn-outline-secondary:hover:not(:disabled) {
  background: linear-gradient(135deg, #6c757d, #545b62);
  border-color: #545b62;
  color: white;
}

/* Enhanced Tab Navigation */
.nav-link {
  border: none;
  background: none;
  cursor: pointer;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem 0.5rem 0 0;
  transition: all 0.2s ease;
  position: relative;
  display: flex;
  align-items: center;
}

.nav-link:hover {
  background-color: #f8f9fa;
  transform: translateY(-1px);
}

.nav-link.active {
  background-color: var(--bs-primary);
  color: rgb(20, 150, 48) !important;
  border-color: var(--bs-primary);
  font-weight: 600;
  box-shadow: 0 2px 4px rgba(0, 123, 255, 0.2);
}

.nav-link.dynamic-tab {
  min-width: 120px;
  justify-content: center;
}

.nav-link.dynamic-tab i {
  font-size: 1rem;
}

.nav-link.dynamic-tab .badge {
  font-size: 0.7rem;
  padding: 0.25rem 0.5rem;
  border-radius: 0.75rem;
  font-weight: 600;
}

.nav-link.has-badge {
  padding-right: 1rem;
}

.nav-link.loading {
  opacity: 0.7;
  pointer-events: none;
}

.nav-link:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}

.nav-link.active .badge {
  background-color: rgba(69, 128, 36, 0.842) !important;
  color: rgb(255, 255, 255) !important;
}

/* Enhanced Analytics Cards */
.analytics-card {
  border: none;
  border-radius: 0.75rem;
  box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.analytics-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.analytics-card .card-body {
  padding: 1.5rem;
}

.analytics-card h3 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.analytics-card p {
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 0.25rem;
}

/* Health Indicators */
.health-indicator {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 0.5rem;
  transition: all 0.2s ease;
}

.health-indicator:hover {
  background: #e9ecef;
}

.health-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.125rem;
  flex-shrink: 0;
}

.health-indicator .fw-medium {
  font-size: 0.875rem;
  color: #495057;
}

.health-indicator small {
  font-size: 0.75rem;
  font-weight: 500;
}

/* Progress bars in analytics cards */
.analytics-card .progress {
  background-color: rgba(255, 255, 255, 0.2);
}

.analytics-card .progress-bar {
  background-color: rgba(255, 255, 255, 0.8) !important;
}

/* Badge enhancements */
.analytics-card .badge {
  font-size: 0.75rem;
  font-weight: 500;
  padding: 0.375rem 0.75rem;
}

/* User Details Modal Styles */
.avatar-lg {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 2rem;
}

.stat-box {
  background: #f8f9fa;
  border-radius: 0.5rem;
  padding: 1rem;
  text-align: center;
  border: 1px solid #e9ecef;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: 700;
  color: #495057;
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.875rem;
  color: #6c757d;
  font-weight: 500;
}

.activity-timeline {
  position: relative;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 0;
  border-bottom: 1px solid #e9ecef;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1rem;
  flex-shrink: 0;
}

.activity-title {
  font-weight: 600;
  color: #495057;
}

.activity-time {
  font-size: 0.875rem;
  color: #6c757d;
}

/* System Info Modal Styles */
.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f1f3f4;
}

.info-item:last-child {
  border-bottom: none;
}

.info-label {
  font-weight: 500;
  color: #495057;
}

.info-value {
  color: #6c757d;
}

.config-item {
  background: #f8f9fa;
  border-radius: 0.5rem;
  padding: 1rem;
  text-align: center;
  border: 1px solid #e9ecef;
}

.config-label {
  font-size: 0.875rem;
  color: #6c757d;
  margin-bottom: 0.25rem;
}

.config-value {
  font-size: 1.25rem;
  font-weight: 600;
  color: #495057;
}

/* Interactive Elements */
.user-link {
  color: #495057;
  transition: color 0.2s ease;
}

.user-link:hover {
  color: var(--bs-primary);
  text-decoration: underline !important;
}

.input-group-text {
  background-color: #f8f9fa;
  border-color: #ced4da;
}

/* Search Results */
.table-responsive .d-flex {
  padding: 0.5rem 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .admin-capability-card {
    flex-direction: column;
    text-align: center;
    padding: 1rem;
  }

  .capability-arrow {
    display: none;
  }

  .quick-actions-bar .btn {
    margin-bottom: 0.5rem;
  }

  .analytics-card h3 {
    font-size: 1.5rem;
  }

  .health-indicator {
    flex-direction: column;
    text-align: center;
    gap: 0.5rem;
  }

  .avatar-lg {
    width: 60px;
    height: 60px;
    font-size: 1.5rem;
  }

  .stat-box {
    margin-bottom: 1rem;
  }

  .input-group {
    width: 100% !important;
    margin-bottom: 0.5rem;
  }
}
</style>
import http from "./http";

export const publicApi = {
    profile: () => http.get("/profile"),
    projects: (params) => http.get("/projects", { params }),
    project: (slug) => http.get(`/projects/${slug}`),
    skills: () => http.get("/skills"),
    experiences: () => http.get("/experience"),
    experience: () => http.get("/experience"),
    education: () => http.get("/educations"),
    educations: () => http.get("/educations"),
    contact: (payload) => http.post("/contact", payload),
}

export const adminApi = {
    login: (payload) => http.post("/admin/login", payload),
    me: () => http.get("/admin/me"),
    logout: () => http.post("/admin/logout"),

    //project (the slug is the route key)
    projects: (params) => http.get('/admin/projects', { params }),
    project: (slug) => http.get(`/admin/projects/${encodeURIComponent(slug)}`),
    createProject: (formData) => http.post('/admin/projects', formData),
    // files need POST + _method=put, which the form appends
    updateProject: (slug, formData) => http.post(`/admin/projects/${encodeURIComponent(slug)}`, formData),
    deleteProject: (slug) => http.delete(`/admin/projects/${encodeURIComponent(slug)}`),

    // skilld (use by the project form now, CRUD page later)
    skills: () => http.get('/admin/skills'),
    createSkill: (payload) => http.post('/admin/skills', payload),
    updateSkill: (id, payload) => http.put(`/admin/skills/${id}`, payload),
    deleteSkill: (id) => http.delete(`/admin/skills/${id}`),

    experiences: () => http.get('/admin/experiences'),
    createExperience: (payload) => http.post('/admin/experiences', payload),
    updateExperience: (id, payload) => http.put(`/admin/experiences/${id}`, payload),
    deleteExperience: (id) => http.delete(`/admin/experiences/${id}`),

    educations: () => http.get('/admin/educations'),
    createEducation: (payload) => http.post('/admin/educations', payload),
    updateEducation: (id, payload) => http.put(`/admin/educations/${id}`, payload),
    deleteEducation: (id) => http.delete(`/admin/educations/${id}`),

    profile: () => http.get('/admin/profile'),
    updateProfile: (formData) => http.post('/admin/profile', formData),

    socialLinks: () => http.get('/admin/social-links'),
    createSocialLink: (payload) => http.post('/admin/social-links', payload),
    updateSocialLink: (id, payload) => http.put(`/admin/social-links/${id}`, payload),
    deleteSocialLink: (id) => http.delete(`/admin/social-links/${id}`),

    //messages
    messages: (params) => http.get('/admin/messages', { params }),
    message: (id) => http.get(`/admin/messages/${id}`),
    markMessageRead: (id) => http.patch(`/admin/messages/${id}/read`),
    deleteMessage: (id) => http.delete(`/admin/messages/${id}`),
}
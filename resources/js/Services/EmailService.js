class EmailService {
    constructor(baseUrl = '/api/v1') {
        this.baseUrl = baseUrl;
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    }

    async request(endpoint, options = {}) {
        try {
            const response = await fetch(`${this.baseUrl}${endpoint}`, {
                ...options,
                headers: { ...this.headers, ...options.headers },
            });

            const data = await response.json();

            if (!response.ok) {
                throw new EmailServiceError(
                    data.error?.message || 'Request failed',
                    response.status,
                    data
                );
            }

            return data;
        } catch (error) {
            if (error instanceof EmailServiceError) {
                throw error;
            }
            throw new EmailServiceError('Network error', 0, error);
        }
    }

    // Draft email
    async draftEmail(description, tone = 'professional', context = null) {
        return this.request('/email/draft', {
            method: 'POST',
            body: JSON.stringify({
                description,
                tone,
                context,
            }),
        });
    }

    // Generate response
    async generateResponse(originalEmail, instructions = null, tone = 'professional') {
        return this.request('/email/response', {
            method: 'POST',
            body: JSON.stringify({
                original_email: originalEmail,
                instructions,
                tone,
            }),
        });
    }

    // Analyze email
    async analyzeEmail(emailContent) {
        return this.request('/email/analyze', {
            method: 'POST',
            body: JSON.stringify({
                email_content: emailContent,
            }),
        });
    }

    // Summarize thread
    async summarizeThread(emailThread) {
        return this.request('/email/summarize', {
            method: 'POST',
            body: JSON.stringify({
                email_thread: emailThread,
            }),
        });
    }

    // Generate template
    async generateTemplate(templateType, context = null) {
        return this.request('/email/template', {
            method: 'POST',
            body: JSON.stringify({
                template_type: templateType,
                context,
            }),
        });
    }

    // Get history
    async getHistory(includeContent = false) {
        const params = includeContent ? '?include_content=true' : '';
        return this.request(`/email/history${params}`, {
            method: 'GET',
        });
    }

    // Rate generation
    async rateGeneration(id, rating, feedback = null) {
        return this.request(`/email/history/${id}/rate`, {
            method: 'POST',
            body: JSON.stringify({
                rating,
                feedback,
            }),
        });
    }
}

class EmailServiceError extends Error {
    constructor(message, status, data) {
        super(message);
        this.name = 'EmailServiceError';
        this.status = status;
        this.data = data;
    }
}

// Export for use in your app
export default EmailService;

// Usage example:
/*
const emailService = new EmailService();

// Draft an email
try {
    const result = await emailService.draftEmail(
        'Request a meeting with the client next week to discuss project updates',
        'professional'
    );

    if (result.success) {
        document.getElementById('email-output').value = result.data.content;
    }
} catch (error) {
    console.error('Error:', error.message);
    alert(error.message);
}
*/
@extends('layouts.app')

@section('title', 'Draft Email')

@section('content')
    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">Draft New Email</h2>

        <form id="emailDraftForm" class="space-y-6">
            @csrf

            <!-- Email Context -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Email Context</h3>

                <div class="space-y-4">
                    <div>
                        <label for="recipient" class="block text-sm font-medium text-gray-700 mb-2">
                            Recipient
                        </label>
                        <input type="email"
                               id="recipient"
                               name="recipient"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="recipient@example.com">
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                            Subject
                        </label>
                        <input type="text"
                               id="subject"
                               name="subject"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="Email subject...">
                    </div>
                </div>
            </div>

            <!-- Email Instructions -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">What do you want to say?</h3>

                <div class="space-y-4">
                    <div>
                        <label for="instructions" class="block text-sm font-medium text-gray-700 mb-2">
                            Describe your email
                        </label>
                        <textarea id="instructions"
                                  name="instructions"
                                  rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="E.g., I need to follow up on our meeting last week about the project timeline. Be professional but friendly..."></textarea>
                    </div>

                    <div>
                        <label for="tone" class="block text-sm font-medium text-gray-700 mb-2">
                            Tone
                        </label>
                        <select id="tone"
                                name="tone"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="professional">Professional</option>
                            <option value="friendly">Friendly</option>
                            <option value="formal">Formal</option>
                            <option value="casual">Casual</option>
                            <option value="urgent">Urgent</option>
                            <option value="apologetic">Apologetic</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit"
                            class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Generate Email
                    </button>
                </div>
            </div>
        </form>

        <!-- Generated Email -->
        <div id="generatedEmail" class="hidden mt-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Generated Email</h3>
                    <div class="space-x-2">
                        <button onclick="copyToClipboard()"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                            Copy
                        </button>
                        <button onclick="regenerateEmail()"
                                class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                            Regenerate
                        </button>
                    </div>
                </div>

                <div id="emailContent" class="prose max-w-none">
                    <!-- Generated content will appear here -->
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="hidden mt-8">
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <div class="animate-pulse-ring w-16 h-16 bg-primary rounded-full mx-auto mb-4"></div>
                <p class="text-gray-600">Generating your email...</p>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('emailDraftForm').addEventListener('submit', async (e) => {
                e.preventDefault();

                // Show loading state
                document.getElementById('generatedEmail').classList.add('hidden');
                document.getElementById('loadingState').classList.remove('hidden');

                // Get form data
                const formData = new FormData(e.target);

                // DEBUG: Check each field individually
                console.log('=== FORM DEBUG ===');
                console.log('Recipient:', formData.get('recipient'));
                console.log('Subject:', formData.get('subject'));
                console.log('Instructions:', formData.get('instructions'));
                console.log('Tone:', formData.get('tone'));

                // Check if form data exists
                console.log('FormData entries:');
                for (let [key, value] of formData.entries()) {
                    console.log(`  ${key}: ${value}`);
                }

                // Create the request body step by step
                const recipient = formData.get('recipient');
                const subject = formData.get('subject');
                const instructions = formData.get('instructions');
                const tone = formData.get('tone');

                console.log('Individual values:');
                console.log('  recipient:', recipient);
                console.log('  subject:', subject);
                console.log('  instructions:', instructions);
                console.log('  tone:', tone);

                // Create the request body
                const requestBody = {
                    recipient: recipient,
                    subject: subject,
                    description: instructions,  // Backend expects "description"
                    tone: tone
                };

                console.log('Request body:', requestBody);
                console.log('Request body JSON:', JSON.stringify(requestBody, null, 2));

                // Check if required fields are filled
                console.log('=== VALIDATION CHECK ===');
                if (!requestBody.description) {
                    console.log('ERROR: Description is missing!');
                    alert('Please fill in the email description!');
                    document.getElementById('loadingState').classList.add('hidden');
                    return;
                }
                console.log('Validation passed - description exists:', requestBody.description);

                console.log('=== MAKING API CALL ===');
                try {
                    console.log('About to fetch:', '{{ route("api.email.generate") }}');
                    console.log('With headers:', {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    });
                    console.log('With body:', JSON.stringify(requestBody));

                    const response = await fetch('{{ route("api.email.generate") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(requestBody)
                    });

                    console.log('=== RESPONSE RECEIVED ===');
                    console.log('Response status:', response.status);
                    console.log('Response ok:', response.ok);
                    console.log('Response headers:', response.headers);

                    // First get the raw response text to see what we're actually getting
                    const responseText = await response.text();
                    console.log('Raw response:', responseText.substring(0, 500));

                    // Try to parse as JSON
                    let data;
                    try {
                        data = JSON.parse(responseText);
                        console.log('Response data:', data);
                    } catch (parseError) {
                        console.error('JSON Parse Error:', parseError);
                        console.log('Response is HTML, not JSON - checking for Laravel errors...');

                        // Show the HTML error in an alert for debugging
                        if (responseText.includes('Exception') || responseText.includes('Error')) {
                            const errorMatch = responseText.match(/<title>(.*?)<\/title>/);
                            const errorTitle = errorMatch ? errorMatch[1] : 'Unknown Laravel Error';
                            alert('Laravel Error: ' + errorTitle);
                        }

                        document.getElementById('loadingState').classList.add('hidden');
                        return;
                    }

                    // Hide loading state
                    document.getElementById('loadingState').classList.add('hidden');

                    if (!response.ok) {
                        console.error('API Error:', data);
                        alert('API Error: ' + (data.message || 'Unknown error'));
                        return;
                    }

                    // Show generated email
                    document.getElementById('generatedEmail').classList.remove('hidden');
                    document.getElementById('emailContent').innerHTML = `
            <div class="mb-4">
                <strong>To:</strong> ${requestBody.recipient || 'Not specified'}<br>
                <strong>Subject:</strong> ${requestBody.subject || 'Not specified'}
            </div>
            <div class="whitespace-pre-wrap">${data.content || data.data?.content || 'No content received'}</div>
        `;
                } catch (error) {
                    console.error('Fetch Error:', error);
                    document.getElementById('loadingState').classList.add('hidden');
                    alert('Network error: ' + error.message);
                }
            });
            function copyToClipboard() {
                const emailContent = document.getElementById('emailContent').innerText;
                navigator.clipboard.writeText(emailContent).then(() => {
                    alert('Email copied to clipboard!');
                });
            }

            function regenerateEmail() {
                document.getElementById('emailDraftForm').dispatchEvent(new Event('submit'));
            }
        </script>
    @endpush
@endsection
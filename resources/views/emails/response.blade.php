@extends('layouts.app')

@section('title', 'Generate Response')

@section('content')
    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">Generate Email Response</h2>

        <form id="responseForm" class="space-y-6">
            @csrf

            <!-- Original Email -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Original Email</h3>

                <div class="space-y-4">
                    <div>
                        <label for="originalEmail" class="block text-sm font-medium text-gray-700 mb-2">
                            Paste the email you want to respond to
                        </label>
                        <textarea id="originalEmail"
                                  name="originalEmail"
                                  rows="8"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Paste the email content here..."></textarea>
                    </div>

                    <div>
                        <label for="responseContext" class="block text-sm font-medium text-gray-700 mb-2">
                            Response context (optional)
                        </label>
                        <textarea id="responseContext"
                                  name="responseContext"
                                  rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Add any specific points you want to address..."></textarea>
                    </div>

                    <div>
                        <label for="responseTone" class="block text-sm font-medium text-gray-700 mb-2">
                            Response tone
                        </label>
                        <select id="responseTone"
                                name="responseTone"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="professional">Professional</option>
                            <option value="friendly">Friendly</option>
                            <option value="formal">Formal</option>
                            <option value="concise">Brief & Concise</option>
                            <option value="detailed">Detailed</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit"
                            class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                        </svg>
                        Generate Response
                    </button>
                </div>
            </div>
        </form>

        <!-- Generated Response -->
        <div id="generatedResponse" class="hidden mt-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Suggested Response</h3>
                    <div class="space-x-2">
                        <button onclick="copyResponse()"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                            Copy
                        </button>
                        <button onclick="regenerateResponse()"
                                class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                            Regenerate
                        </button>
                    </div>
                </div>

                <div id="responseContent" class="prose max-w-none">
                    <!-- Generated response will appear here -->
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="hidden mt-8">
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <div class="animate-pulse-ring w-16 h-16 bg-primary rounded-full mx-auto mb-4"></div>
                <p class="text-gray-600">Generating response...</p>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('responseForm').addEventListener('submit', async (e) => {
                e.preventDefault();

                // Show loading state
                document.getElementById('generatedResponse').classList.add('hidden');
                document.getElementById('loadingState').classList.remove('hidden');

                // Get form data
                const formData = new FormData(e.target);

                try {
                    const response = await fetch('{{ route("api.email.response") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            originalEmail: formData.get('originalEmail'),
                            context: formData.get('responseContext'),
                            tone: formData.get('responseTone')
                        })
                    });

                    const data = await response.json();

                    // Hide loading state
                    document.getElementById('loadingState').classList.add('hidden');

                    // Show generated response
                    document.getElementById('generatedResponse').classList.remove('hidden');
                    document.getElementById('responseContent').innerHTML = `
            <div class="whitespace-pre-wrap">${data.content || 'Response generation coming soon...'}</div>
        `;
                } catch (error) {
                    console.error('Error:', error);
                    document.getElementById('loadingState').classList.add('hidden');
                    alert('Failed to generate response. Please try again.');
                }
            });

            function copyResponse() {
                const responseContent = document.getElementById('responseContent').innerText;
                navigator.clipboard.writeText(responseContent).then(() => {
                    alert('Response copied to clipboard!');
                });
            }

            function regenerateResponse() {
                document.getElementById('responseForm').dispatchEvent(new Event('submit'));
            }
        </script>
    @endpush
@endsection
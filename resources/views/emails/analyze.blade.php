@extends('layouts.app')

@section('title', 'Analyze Email')

@section('content')
    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">Email Analyzer</h2>

        <form id="analyzeForm" class="space-y-6">
            @csrf

            <!-- Email to Analyze -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Email to Analyze</h3>

                <div class="space-y-4">
                    <div>
                        <label for="emailContent" class="block text-sm font-medium text-gray-700 mb-2">
                            Paste your email draft
                        </label>
                        <textarea id="emailContent"
                                  name="emailContent"
                                  rows="10"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Paste the email you want to analyze..."></textarea>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit"
                            class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Analyze Email
                    </button>
                </div>
            </div>
        </form>

        <!-- Analysis Results -->
        <div id="analysisResults" class="hidden mt-8 space-y-6">
            <!-- Tone Analysis -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Tone Analysis</h3>
                <div id="toneResults" class="space-y-3">
                    <!-- Tone analysis will appear here -->
                </div>
            </div>

            <!-- Clarity Score -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Clarity & Readability</h3>
                <div id="clarityResults">
                    <!-- Clarity analysis will appear here -->
                </div>
            </div>

            <!-- Suggestions -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Improvement Suggestions</h3>
                <div id="suggestionResults" class="space-y-2">
                    <!-- Suggestions will appear here -->
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="hidden mt-8">
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <div class="animate-pulse-ring w-16 h-16 bg-primary rounded-full mx-auto mb-4"></div>
                <p class="text-gray-600">Analyzing your email...</p>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('analyzeForm').addEventListener('submit', async (e) => {
                e.preventDefault();

                // Show loading state
                document.getElementById('analysisResults').classList.add('hidden');
                document.getElementById('loadingState').classList.remove('hidden');

                // Get form data
                const formData = new FormData(e.target);

                try {
                    const response = await fetch('{{ route("api.email.analyze") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            content: formData.get('emailContent')
                        })
                    });

                    const data = await response.json();

                    // Hide loading state
                    document.getElementById('loadingState').classList.add('hidden');

                    // Show analysis results
                    document.getElementById('analysisResults').classList.remove('hidden');

                    // Display mock results (replace with actual data when API is ready)
                    displayAnalysisResults({
                        tone: {
                            primary: 'Professional',
                            secondary: 'Friendly',
                            score: 85
                        },
                        clarity: {
                            score: 78,
                            readability: 'Good',
                            avgSentenceLength: 15
                        },
                        suggestions: [
                            'Consider breaking the third paragraph into smaller sentences',
                            'The greeting could be more personalized',
                            'Strong closing - maintains professional tone'
                        ]
                    });
                } catch (error) {
                    console.error('Error:', error);
                    document.getElementById('loadingState').classList.add('hidden');
                    alert('Failed to analyze email. Please try again.');
                }
            });

            function displayAnalysisResults(results) {
                // Tone Results
                document.getElementById('toneResults').innerHTML = `
        <div class="flex items-center justify-between">
            <span class="text-gray-700">Primary Tone:</span>
            <span class="font-semibold">${results.tone.primary}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-gray-700">Secondary Tone:</span>
            <span class="font-semibold">${results.tone.secondary}</span>
        </div>
        <div class="mt-4">
            <div class="flex justify-between mb-1">
                <span class="text-sm text-gray-600">Tone Consistency</span>
                <span class="text-sm font-semibold">${results.tone.score}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-primary h-2 rounded-full" style="width: ${results.tone.score}%"></div>
            </div>
        </div>
    `;

                // Clarity Results
                document.getElementById('clarityResults').innerHTML = `
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-3xl font-bold text-primary">${results.clarity.score}</div>
                <div class="text-sm text-gray-600">Clarity Score</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-3xl font-bold text-green-600">${results.clarity.readability}</div>
                <div class="text-sm text-gray-600">Readability</div>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            Average sentence length: ${results.clarity.avgSentenceLength} words
        </div>
    `;

                // Suggestions
                const suggestionsHTML = results.suggestions.map(suggestion => `
        <div class="flex items-start">
            <svg class="w-5 h-5 text-primary mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="text-gray-700">${suggestion}</span>
        </div>
    `).join('');

                document.getElementById('suggestionResults').innerHTML = suggestionsHTML;
            }
        </script>
    @endpush
@endsection
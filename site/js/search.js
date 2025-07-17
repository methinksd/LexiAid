// LexiAid Semantic Search Frontend Script

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('semantic-search-input');
    const searchButton = document.getElementById('search-button');
    const resultsContainer = document.getElementById('search-results-container');
    const demoResults = document.getElementById('demo-results');
    const resultsSort = document.getElementById('results-sort');

    let currentResults = [];

    // Function to escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Function to format the similarity score as a percentage
    function formatScore(score) {
        return Math.round(score * 100) + '%';
    }

    // Function to generate HTML for a single result card
    function createResultCard(result) {
        const tags = result.tags && result.tags !== null && result.tags !== undefined && Array.isArray(result.tags) 
            ? result.tags.map(tag => `<span class='badge badge-secondary mr-1'>${escapeHtml(tag)}</span>`).join('')
            : '';
            
        return `
            <div class="col-md-6 col-xl-4 mb-4">
                <div class="card h-100 case-card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">${escapeHtml(result.title)}</h5>
                        <span class="badge badge-light">${formatScore(result.similarity_score)}</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            ${tags}
                        </div>
                        <p class="card-text">${escapeHtml(result.summary)}</p>
                        <ul class="list-unstyled mb-2 small">
                            ${result.year ? `<li><strong>Year:</strong> ${escapeHtml(result.year)}</li>` : ''}
                            ${result.citation ? `<li><strong>Citation:</strong> ${escapeHtml(result.citation)}</li>` : ''}
                            ${result.jurisdiction ? `<li><strong>Jurisdiction:</strong> ${escapeHtml(result.jurisdiction)}</li>` : ''}
                            ${result.type ? `<li><strong>Type:</strong> ${escapeHtml(result.type)}</li>` : ''}
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="#" class="btn btn-sm btn-outline-primary mr-2">View Brief</a>
                        <a href="#" class="btn btn-sm btn-outline-secondary">Add to Task</a>
                    </div>
                </div>
            </div>
        `;
    }

    // Function to display search results
    function displayResults(results) {
        const resultsList = document.getElementById('search-results-list');
        
        if (results.length > 0) {
            resultsList.innerHTML = results.map(createResultCard).join('');
            demoResults.style.display = 'none';
            resultsContainer.style.display = 'block';
        } else {
            resultsList.innerHTML = '<div class="col-12"><div class="alert alert-info">No results found. Try adjusting your search terms.</div></div>';
            demoResults.style.display = 'none';
            resultsContainer.style.display = 'block';
        }
    }

    // Function to show loading state
    function showLoading() {
        searchButton.disabled = true;
        searchButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Searching...';
        
        const resultsList = document.getElementById('search-results-list');
        resultsList.innerHTML = `<div class='col-12'><div class='alert alert-info'><span class='spinner-border spinner-border-sm mr-2'></span>Searching for relevant cases...</div></div>`;
        
        demoResults.style.display = 'none';
        resultsContainer.style.display = 'block';
    }

    // Function to reset button state
    function resetButton() {
        searchButton.disabled = false;
        searchButton.innerHTML = '<i class="fa fa-search mr-2"></i> Search';
    }

    // Function to perform the search
    async function performSearch() {
        const query = searchInput.value.trim();
        if (!query) {
            const resultsList = document.getElementById('search-results-list');
            resultsList.innerHTML = `<div class='col-12'><div class='alert alert-warning'>Please enter a search query.</div></div>`;
            demoResults.style.display = 'none';
            resultsContainer.style.display = 'block';
            return;
        }
        
        showLoading();
        
        try {
            console.log('Sending search request for:', query);
            
            const response = await fetch('search.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    query: query,
                    top_k: 10
                })
            });

            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Search response:', data);
            
            if (data.status === 'success') {
                currentResults = data.results || [];
                displayResults(currentResults);
                
                // Show success message if results found
                if (currentResults.length > 0) {
                    console.log(`Found ${currentResults.length} results using ${data.search_method || 'unknown'} method`);
                }
            } else {
                throw new Error(data.message || 'Search failed');
            }
        } catch (error) {
            console.error('Search error:', error);
            const resultsList = document.getElementById('search-results-list');
            resultsList.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger">
                        <h6>Search Error</h6>
                        <p>An error occurred while searching. Please try again later.</p>
                        <small class="text-muted">Error: ${error.message || error}</small>
                        <hr>
                        <small class="text-muted">
                            <strong>Troubleshooting:</strong><br>
                            1. Check that your web server is running<br>
                            2. Verify database connection is working<br>
                            3. Check browser console for more details
                        </small>
                    </div>
                </div>
            `;
            demoResults.style.display = 'none';
            resultsContainer.style.display = 'block';
        } finally {
            resetButton();
        }
    }
    // Event Listeners
    searchButton.addEventListener('click', performSearch);
    
    searchInput.addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            performSearch();
        }
    });

    // Handle sorting
    if (resultsSort) {
        resultsSort.addEventListener('change', function() {
            if (currentResults.length > 0) {
                let sorted = [...currentResults];

                // Apply sorting
                sorted.sort((a, b) => {
                    switch (resultsSort.value) {
                        case 'date-desc':
                            return (b.year || 0) - (a.year || 0);
                        case 'date-asc':
                            return (a.year || 0) - (b.year || 0);
                        case 'citations':
                            return (b.citations || 0) - (a.citations || 0);
                        default: // relevance
                            return b.similarity_score - a.similarity_score;
                    }
                });

                displayResults(sorted);
            }
        });
    }

    // Add test function for debugging
    window.testSearch = function(query = 'constitutional law') {
        searchInput.value = query;
        performSearch();
    };

    // Log that the search script is loaded
    console.log('LexiAid Search module loaded successfully');
    console.log('Use testSearch("your query") to test the search functionality');
});

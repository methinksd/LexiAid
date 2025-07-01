// LexiAid Semantic Search Frontend Script

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('semantic-search-input');
    const searchButton = document.getElementById('search-button');
    const resultsContainer = document.getElementById('search-results-container');
    const dateFilter = document.getElementById('date-filter');
    const topicFilter = document.getElementById('topic-filter');
    const courtFilter = document.getElementById('court-filter');
    const resultsSort = document.getElementById('results-sort');

    let currentResults = [];

    // Function to format the similarity score as a percentage
    function formatScore(score) {
        return Math.round(score * 100) + '%';
    }

    // Function to generate HTML for a single result card
    function createResultCard(result) {
        return `
            <div class="col-md-6 col-xl-4 mb-4">
                <div class="card h-100 case-card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">${result.title}</h5>
                        <span class="badge badge-light">${formatScore(result.similarity_score)}</span>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><strong>Summary:</strong> ${result.summary}</p>
                        <ul class="list-unstyled mb-2">
                            <li><strong>Year:</strong> ${result.year || 'N/A'}</li>
                            <li><strong>Tags:</strong> ${result.tags && result.tags.length ? result.tags.map(tag => `<span class='badge badge-secondary mr-1'>${tag}</span>`).join('') : 'None'}</li>
                        </ul>
                        ${result.citation ? `<div><strong>Citation:</strong> ${result.citation}</div>` : ''}
                    </div>
                    <div class="card-footer bg-light">
                        ${result.jurisdiction ? `<small class="text-muted">Jurisdiction: ${result.jurisdiction}</small><br>` : ''}
                        ${result.type ? `<small class="text-muted">Type: ${result.type}</small>` : ''}
                    </div>
                </div>
            </div>
        `;
    }

    // Function to display search results
    function displayResults(results) {
        const resultsHTML = results.length > 0 
            ? results.map(createResultCard).join('')
            : '<div class="col-12"><div class="alert alert-info">No results found. Try adjusting your search terms.</div></div>';
        document.querySelector('#search-results-container .row').innerHTML = resultsHTML;
    }

    // Function to perform the search
    async function performSearch() {
        const query = searchInput.value.trim();
        if (!query) {
            document.querySelector('#search-results-container .row').innerHTML = `<div class='col-12'><div class='alert alert-warning'>Please enter a search query.</div></div>`;
            return;
        }
        // Show loading state
        searchButton.disabled = true;
        searchButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Searching...';
        document.querySelector('#search-results-container .row').innerHTML = `<div class='col-12'><div class='alert alert-info'><span class='spinner-border spinner-border-sm mr-2'></span>Searching for relevant cases...</div></div>`;
        
        try {
            const response = await fetch('search.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    query: query,
                    top_k: 10, // Request more results for filtering
                    filters: {
                        date: dateFilter.value,
                        topic: topicFilter.value,
                        court: courtFilter.value
                    }
                })
            });

            const data = await response.json();
            
            if (data.status === 'success') {
                currentResults = data.results;
                displayResults(data.results);
                resultsContainer.scrollIntoView({ behavior: 'smooth' });
            } else {
                throw new Error(data.message || 'Search failed');
            }
        } catch (error) {
            console.error('Search error:', error);
            document.querySelector('#search-results-container .row').innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger">
                        An error occurred while searching. Please try again later.<br>
                        <small>${error.message || error}</small>
                    </div>
                </div>
            `;
        } finally {
            // Reset button state
            searchButton.disabled = false;
            searchButton.innerHTML = '<i class="fa fa-search mr-2"></i> Search';
        }
    }

    // Event Listeners
    searchButton.addEventListener('click', performSearch);
    
    searchInput.addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            performSearch();
        }
    });

    // Handle filters and sorting
    [dateFilter, topicFilter, courtFilter, resultsSort].forEach(filter => {
        filter.addEventListener('change', function() {
            if (currentResults.length > 0) {
                let filtered = [...currentResults];

                // Apply filters
                if (dateFilter.value) {
                    const currentYear = new Date().getFullYear();
                    filtered = filtered.filter(result => {
                        const year = result.year || currentYear;
                        switch (dateFilter.value) {
                            case 'recent': return currentYear - year <= 5;
                            case 'decade': return currentYear - year <= 10;
                            case 'century': return currentYear - year <= 100;
                            default: return true;
                        }
                    });
                }

                if (topicFilter.value) {
                    filtered = filtered.filter(result => 
                        result.tags && result.tags.some(tag => 
                            tag.toLowerCase().includes(topicFilter.value.toLowerCase())
                        )
                    );
                }

                if (courtFilter.value) {
                    filtered = filtered.filter(result => 
                        result.jurisdiction && result.jurisdiction.toLowerCase().includes(courtFilter.value.toLowerCase())
                    );
                }

                // Apply sorting
                filtered.sort((a, b) => {
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

                displayResults(filtered);
            }
        });
    });
});

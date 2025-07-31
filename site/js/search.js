// LexiAid Semantic Search Frontend Script - Enhanced Version

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('semantic-search-input');
    const searchButton = document.getElementById('search-button');
    const resultsContainer = document.getElementById('search-results-container');
    const demoResults = document.getElementById('demo-results');
    const resultsSort = document.getElementById('results-sort');

    let currentResults = [];
    let isSearching = false;

    // Function to escape HTML to prevent XSS
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Function to format the similarity score as a percentage
    function formatScore(score) {
        return Math.round(score * 100) + '%';
    }

    // Function to show loading state
    function showLoadingState() {
        const loadingHTML = '<div class="col-12"><div class="loading-container fade-in"><div class="loading-spinner"></div><div class="loading-text"><strong>Searching legal documents...</strong><br><small>Using AI to find the most relevant results</small></div></div></div>';
        
        document.getElementById('search-results-list').innerHTML = loadingHTML;
        resultsContainer.style.display = 'block';
        demoResults.style.display = 'none';
    }

    // Function to show error state
    function showErrorState(message) {
        const errorHTML = '<div class="col-12"><div class="alert alert-warning fade-in" role="alert"><div class="d-flex align-items-center"><i class="fas fa-exclamation-triangle fa-2x text-warning mr-3"></i><div><strong>Search Unavailable</strong><br><small>' + escapeHtml(message) + '</small></div></div></div></div>';
        
        document.getElementById('search-results-list').innerHTML = errorHTML;
    }

    // Function to show no results state
    function showNoResultsState(query) {
        const noResultsHTML = '<div class="col-12"><div class="no-data-placeholder fade-in"><div class="no-data-icon"><i class="fas fa-search"></i></div><h4>No Results Found</h4><p>We could not find any legal documents matching "<strong>' + escapeHtml(query) + '</strong>"</p><div class="mt-3"><small class="text-muted"><i class="fas fa-lightbulb text-warning mr-1"></i>Try different keywords or broader search terms</small></div></div></div>';
        
        document.getElementById('search-results-list').innerHTML = noResultsHTML;
    }

    // Function to generate HTML for a single result card
    function createResultCard(result) {
        const tags = result.tags && Array.isArray(result.tags) 
            ? result.tags.map(tag => '<span class="badge badge-secondary mr-1">' + escapeHtml(tag) + '</span>').join('')
            : '';
            
        const similarityClass = result.similarity_score >= 0.8 ? 'bg-success' : 
                               result.similarity_score >= 0.6 ? 'bg-warning' : 'bg-info';
            
        return '<div class="col-md-6 col-xl-4 mb-4 fade-in"><div class="card result-card-enhanced h-100"><div class="card-header bg-primary text-white d-flex justify-content-between align-items-center"><h5 class="mb-0">' + escapeHtml(result.title) + '</h5><span class="similarity-badge ' + similarityClass + '">' + formatScore(result.similarity_score) + '</span></div><div class="card-body"><div class="mb-3">' + tags + '</div><p class="card-text">' + escapeHtml(result.summary) + '</p><ul class="list-unstyled mb-2 small">' + (result.year ? '<li><strong>Year:</strong> ' + escapeHtml(result.year) + '</li>' : '') + (result.citation ? '<li><strong>Citation:</strong> ' + escapeHtml(result.citation) + '</li>' : '') + (result.jurisdiction ? '<li><strong>Jurisdiction:</strong> ' + escapeHtml(result.jurisdiction) + '</li>' : '') + (result.type ? '<li><strong>Type:</strong> ' + escapeHtml(result.type) + '</li>' : '') + '</ul></div><div class="card-footer bg-light"><div class="btn-group btn-group-sm w-100" role="group"><button type="button" class="btn btn-outline-primary" onclick="viewBrief(\'' + (result.id || result.title) + '\')"><i class="fas fa-eye mr-1"></i>View Brief</button><button type="button" class="btn btn-outline-secondary" onclick="saveTasks(\'' + (result.id || result.title) + '\')"><i class="fas fa-bookmark mr-1"></i>Save</button></div></div></div></div>';
    }

    // Function to display search results
    function displayResults(results) {
        const resultsList = document.getElementById('search-results-list');
        
        if (results.length > 0) {
            resultsList.innerHTML = results.map(createResultCard).join('');
            demoResults.style.display = 'none';
            resultsContainer.style.display = 'block';
        } else {
            showNoResultsState(searchInput.value);
        }
    }

    // Function to perform the search
    async function performSearch() {
        const query = searchInput.value.trim();
        
        if (!query) {
            alert('Please enter a search query.');
            return;
        }

        if (isSearching) {
            return; // Prevent multiple simultaneous searches
        }

        isSearching = true;
        searchButton.disabled = true;
        searchButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Searching...';
        
        showLoadingState();

        try {
            // Try semantic search first
            const response = await fetch('search.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    query: query,
                    method: 'semantic'
                })
            });

            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }

            const data = await response.json();
            
            if (data.status === 'success') {
                currentResults = data.results || [];
                if (currentResults.length > 0) {
                    console.log('Found ' + currentResults.length + ' results using ' + (data.search_method || 'unknown') + ' method');
                    displayResults(currentResults);
                } else {
                    showNoResultsState(query);
                }
            } else {
                throw new Error(data.message || 'Search failed');
            }

        } catch (error) {
            console.error('Search error:', error);
            showErrorState('Search service temporarily unavailable. Please try again later.');
        } finally {
            isSearching = false;
            searchButton.disabled = false;
            searchButton.innerHTML = '<i class="fas fa-search mr-2"></i>Search';
        }
    }

    // Function to sort results
    function sortResults(criteria) {
        if (currentResults.length === 0) return;

        switch (criteria) {
            case 'relevance':
                currentResults.sort((a, b) => (b.similarity_score || 0) - (a.similarity_score || 0));
                break;
            case 'date-desc':
                currentResults.sort((a, b) => (b.year || 0) - (a.year || 0));
                break;
            case 'date-asc':
                currentResults.sort((a, b) => (a.year || 0) - (b.year || 0));
                break;
            case 'citations':
                currentResults.sort((a, b) => (b.citations || 0) - (a.citations || 0));
                break;
        }

        displayResults(currentResults);
    }

    // Event listeners
    searchButton.addEventListener('click', performSearch);
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
    });

    if (resultsSort) {
        resultsSort.addEventListener('change', function() {
            sortResults(this.value);
        });
    }

    // Input validation and suggestions
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        if (query.length > 2) {
            // Could add search suggestions here in the future
        }
    });

    // Set current copyright year
    const copyrightYear = document.getElementById('copyright-year');
    if (copyrightYear) {
        copyrightYear.textContent = new Date().getFullYear();
    }
});

// Global helper functions for result card actions
function viewBrief(caseId) {
    console.log('Viewing brief for:', caseId);
    // Redirect to case brief viewer
    window.location.href = 'case-brief.html?id=' + encodeURIComponent(caseId);
}

function saveTasks(caseId) {
    console.log('Saving to tasks:', caseId);
    // Could integrate with tasks system
    const confirmed = confirm('Add this case to your task list?');
    if (confirmed) {
        // Add to tasks via AJAX
        alert('Case added to your tasks!');
    }
}

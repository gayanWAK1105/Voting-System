// Poll Data
const pollsData = [
    {
        id: 1,
        question: "What's your favorite programming language?",
        category: "tech",
        options: ["Python", "JavaScript", "Java", "C++"],
        votes: [125, 95, 68, 42],
        totalVotes: 330,
        createdBy: "admin",
        createdAt: new Date(Date.now() - 604800000),
        expiresAt: new Date(Date.now() + 432000000), // 5 days
        allowMultiple: false
    },
    {
        id: 2,
        question: "Best time to schedule team meetings?",
        category: "work",
        options: ["Morning (9-12 AM)", "Afternoon (1-5 PM)", "Evening (5-7 PM)", "Flexible"],
        votes: [142, 89, 35, 78],
        totalVotes: 344,
        createdBy: "manager",
        createdAt: new Date(Date.now() - 1209600000),
        expiresAt: new Date(Date.now() + 86400000), // 1 day
        allowMultiple: false
    },
    {
        id: 3,
        question: "Which streaming service do you use most?",
        category: "entertainment",
        options: ["Netflix", "Amazon Prime", "Disney+", "Hulu", "Others"],
        votes: [234, 167, 156, 89, 45],
        totalVotes: 691,
        createdBy: "user123",
        createdAt: new Date(Date.now() - 1814400000),
        expiresAt: new Date(Date.now() + 604800000), // 7 days
        allowMultiple: false
    },
    {
        id: 4,
        question: "Should remote work be permanent?",
        category: "work",
        options: ["Yes, always remote", "Hybrid is better", "No, back to office", "Depends on role"],
        votes: [289, 412, 156, 203],
        totalVotes: 1060,
        createdBy: "hr_admin",
        createdAt: new Date(Date.now() - 2419200000),
        expiresAt: new Date(Date.now() + 172800000), // 2 days
        allowMultiple: false
    },
    {
        id: 5,
        question: "What's your preferred work setup?",
        category: "lifestyle",
        options: ["Home office", "Coffee shop", "Co-working space", "Traditional office"],
        votes: [367, 134, 89, 210],
        totalVotes: 800,
        createdBy: "freelancer",
        createdAt: new Date(Date.now() - 3024000000),
        expiresAt: new Date(Date.now() + 259200000), // 3 days
        allowMultiple: false
    },
    {
        id: 6,
        question: "Best framework for web development?",
        category: "tech",
        options: ["React", "Vue.js", "Angular", "Svelte", "Next.js"],
        votes: [456, 234, 189, 167, 298],
        totalVotes: 1344,
        createdBy: "developer",
        createdAt: new Date(Date.now() - 864000000),
        expiresAt: new Date(Date.now() + 518400000), // 6 days
        allowMultiple: false
    },
    {
        id: 7,
        question: "How often do you exercise?",
        category: "lifestyle",
        options: ["Daily", "3-4 times a week", "1-2 times a week", "Rarely", "Never"],
        votes: [234, 567, 345, 189, 65],
        totalVotes: 1400,
        createdBy: "health_coach",
        createdAt: new Date(Date.now() - 1296000000),
        expiresAt: new Date(Date.now() + 345600000), // 4 days
        allowMultiple: false
    },
    {
        id: 8,
        question: "Preferred method for code reviews?",
        category: "tech",
        options: ["GitHub Pull Requests", "GitLab Merge Requests", "Gerrit", "Email", "In-person"],
        votes: [567, 345, 123, 45, 89],
        totalVotes: 1169,
        createdBy: "devops",
        createdAt: new Date(Date.now() - 432000000),
        expiresAt: new Date(Date.now() + 777600000), // 9 days
        allowMultiple: false
    }
];

let currentPolls = [...pollsData];
let selectedPollId = null;
let selectedOption = null;

// Initialize polls on page load
document.addEventListener('DOMContentLoaded', function() {
    displayPolls(currentPolls);
    setupEventListeners();
});

// Setup event listeners for filters and search
function setupEventListeners() {
    document.getElementById('searchInput').addEventListener('input', filterAndSortPolls);
    document.getElementById('categoryFilter').addEventListener('change', filterAndSortPolls);
    document.getElementById('sortFilter').addEventListener('change', filterAndSortPolls);
}

// Filter and sort polls
function filterAndSortPolls() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;
    const sortBy = document.getElementById('sortFilter').value;

    // Filter
    currentPolls = pollsData.filter(poll => {
        const matchesSearch = poll.question.toLowerCase().includes(searchTerm);
        const matchesCategory = !category || poll.category === category;
        return matchesSearch && matchesCategory;
    });

    // Sort
    switch(sortBy) {
        case 'newest':
            currentPolls.sort((a, b) => b.createdAt - a.createdAt);
            break;
        case 'trending':
            currentPolls.sort((a, b) => b.totalVotes - a.totalVotes);
            break;
        case 'ending':
            currentPolls.sort((a, b) => a.expiresAt - b.expiresAt);
            break;
    }

    displayPolls(currentPolls);
}

// Calculate percentage
function getPercentage(votes, total) {
    if (total === 0) return 0;
    return Math.round((votes / total) * 100);
}

// Format time remaining
function getTimeRemaining(expiresAt) {
    const now = new Date();
    const diff = expiresAt - now;
    
    if (diff < 0) return 'Expired';
    
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    
    if (days > 0) {
        return `${days}d ${hours}h remaining`;
    } else if (hours > 0) {
        return `${hours}h remaining`;
    } else {
        return 'Expires soon';
    }
}

// Check if poll is expiring soon
function isExpiringSoon(expiresAt) {
    const now = new Date();
    const hoursUntilExpiry = (expiresAt - now) / (1000 * 60 * 60);
    return hoursUntilExpiry < 24 && hoursUntilExpiry > 0;
}

// Display polls
function displayPolls(polls) {
    const pollsGrid = document.getElementById('pollsGrid');
    const emptyState = document.getElementById('emptyState');
    
    if (polls.length === 0) {
        pollsGrid.innerHTML = '';
        emptyState.style.display = 'block';
        return;
    }
    
    emptyState.style.display = 'none';
    
    pollsGrid.innerHTML = polls.map(poll => {
        const topVoteIndex = poll.votes.indexOf(Math.max(...poll.votes));
        const topOption = poll.options[topVoteIndex];
        
        return `
            <div class="poll-card">
                <div class="poll-card-header">
                    <h3>${poll.question}</h3>
                    <span class="poll-badge ${poll.category}">${getCategoryLabel(poll.category)}</span>
                </div>
                <div class="poll-card-content">
                    <div class="poll-options-preview">
                        ${poll.options.slice(0, 3).map((option, index) => {
                            const percentage = getPercentage(poll.votes[index], poll.totalVotes);
                            return `
                                <div class="option-item">
                                    <span class="option-label">${option}</span>
                                    <div class="option-bar">
                                        <div class="option-fill" style="width: ${percentage}%"></div>
                                    </div>
                                    <span class="option-percent">${percentage}%</span>
                                </div>
                            `;
                        }).join('')}
                        ${poll.options.length > 3 ? `<p style="color: #999; font-size: 12px;">+${poll.options.length - 3} more options</p>` : ''}
                    </div>
                    <div class="poll-info">
                        <div class="poll-stats">
                            <div class="poll-stat">
                                📊 <strong>${poll.totalVotes}</strong> votes
                            </div>
                            <div class="poll-stat">
                                👤 <strong>${Math.floor(poll.totalVotes * 0.8)}</strong> voters
                            </div>
                        </div>
                        <div class="poll-time ${isExpiringSoon(poll.expiresAt) ? 'expiring-soon' : ''}">
                            ${getTimeRemaining(poll.expiresAt)}
                        </div>
                    </div>
                </div>
                <button onclick="openVoteModal(${poll.id})">Vote Now</button>
            </div>
        `;
    }).join('');
}

// Get category label
function getCategoryLabel(category) {
    const labels = {
        'tech': '💻 Tech',
        'work': '💼 Work',
        'lifestyle': '🎯 Lifestyle',
        'entertainment': '🎬 Entertainment',
        'politics': '🏛️ Politics'
    };
    return labels[category] || category;
}

// Open vote modal
function openVoteModal(pollId) {
    selectedPollId = pollId;
    selectedOption = null;
    
    const poll = pollsData.find(p => p.id === pollId);
    if (!poll) return;
    
    // Set modal content
    document.getElementById('modalPollQuestion').textContent = poll.question;
    document.getElementById('totalVotesCount').textContent = poll.totalVotes;
    document.getElementById('expiresCount').textContent = getTimeRemaining(poll.expiresAt);
    
    // Create vote options
    const optionsContainer = document.getElementById('voteOptionsContainer');
    optionsContainer.innerHTML = poll.options.map((option, index) => {
        return `
            <div class="vote-option" onclick="selectOption(${index}, '${option}')">
                <input type="radio" id="option_${index}" name="voteOption" value="${index}">
                <label for="option_${index}">${option}</label>
            </div>
        `;
    }).join('');
    
    // Open modal
    const modal = document.getElementById('voteModal');
    modal.classList.add('active');
}

// Close vote modal
function closeVoteModal() {
    const modal = document.getElementById('voteModal');
    modal.classList.remove('active');
    selectedPollId = null;
    selectedOption = null;
}

// Select option
function selectOption(index, optionText) {
    selectedOption = index;
    
    // Update UI
    document.querySelectorAll('.vote-option').forEach((el, i) => {
        el.classList.toggle('selected', i === index);
    });
    document.getElementById(`option_${index}`).checked = true;
}

// Submit vote
function submitVote() {
    if (selectedOption === null) {
        alert('Please select an option');
        return;
    }
    
    const poll = pollsData.find(p => p.id === selectedPollId);
    if (!poll) return;
    
    // Record vote
    poll.votes[selectedOption]++;
    poll.totalVotes++;
    
    console.log(`Voted for option: ${poll.options[selectedOption]}`);
    alert(`Your vote for "${poll.options[selectedOption]}" has been recorded!`);
    
    closeVoteModal();
    displayPolls(currentPolls);
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('voteModal');
    if (event.target === modal) {
        closeVoteModal();
    }
});

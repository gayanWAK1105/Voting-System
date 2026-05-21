// Modal Functions
function openCreatePollModal() {
    const modal = document.getElementById('createPollModal');
    modal.classList.add('active');
}

function closeCreatePollModal() {
    const modal = document.getElementById('createPollModal');
    modal.classList.remove('active');
}

// Close modal when clicking outside of it
window.addEventListener('click', function(event) {
    const modal = document.getElementById('createPollModal');
    if (event.target === modal) {
        modal.classList.remove('active');
    }
});

// Add Poll Option
function addPollOption() {
    const optionsContainer = document.getElementById('optionsContainer');
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'poll-option-input';
    input.placeholder = `Option ${optionsContainer.children.length + 1}`;
    optionsContainer.appendChild(input);
}

// Create Poll Form Submission
document.getElementById('createPollForm').addEventListener('submit', function(event) {
    event.preventDefault();
    
    const question = document.getElementById('pollQuestion').value;
    const duration = document.getElementById('pollDuration').value;
    const allowMultiple = document.getElementById('allowMultiple').checked;
    
    const options = Array.from(document.querySelectorAll('.poll-option-input'))
        .map(input => input.value)
        .filter(value => value.trim() !== '');
    
    if (options.length < 2) {
        alert('Please provide at least 2 options for the poll.');
        return;
    }
    
    // Create poll object
    const pollData = {
        question: question,
        options: options,
        duration: duration,
        allowMultiple: allowMultiple,
        createdAt: new Date().toISOString()
    };
    
    console.log('Poll Created:', pollData);
    alert(`Poll "${question}" created successfully!`);
    
    // Reset form
    document.getElementById('createPollForm').reset();
    closeCreatePollModal();
    
    // In a real application, you would send this data to a server
    // fetch('/api/polls', {
    //     method: 'POST',
    //     headers: { 'Content-Type': 'application/json' },
    //     body: JSON.stringify(pollData)
    // })
});

// View Polls Function
function viewPolls() {
    console.log('Navigating to vote page...');
    window.location.href = 'vote.html';
}

// View History Function
function viewHistory() {
    console.log('Viewing voting history...');
    // Show user's voting history
    const history = [
        {
            poll: 'What\'s your favorite programming language?',
            vote: 'Python',
            date: new Date(Date.now() - 86400000).toLocaleDateString()
        },
        {
            poll: 'Best time to schedule meetings?',
            vote: 'Morning (9-12 AM)',
            date: new Date(Date.now() - 172800000).toLocaleDateString()
        }
    ];
    
    let historyText = 'Your Voting History:\n\n';
    history.forEach(item => {
        historyText += `Poll: ${item.poll}\nYour Vote: ${item.vote}\nDate: ${item.date}\n\n`;
    });
    
    alert(historyText);
}

// View My Polls Function
function viewMyPolls() {
    console.log('Viewing my polls...');
    const myPolls = [
        {
            question: 'What\'s your favorite programming language?',
            votes: 523,
            status: 'Active'
        },
        {
            question: 'Best time to schedule meetings?',
            votes: 342,
            status: 'Active'
        }
    ];
    
    let pollsText = 'Your Polls:\n\n';
    myPolls.forEach((poll, index) => {
        pollsText += `${index + 1}. ${poll.question}\n   Votes: ${poll.votes} | Status: ${poll.status}\n\n`;
    });
    
    alert(pollsText);
}

// Vote on Poll Function
function votePoll() {
    console.log('Opening vote dialog...');
    alert('Vote feature coming soon! You will be able to select an option and submit your vote.');
}

// Dynamic Polling System
class PollManager {
    constructor() {
        this.polls = [];
        this.userVotes = {};
        this.loadPolls();
    }

    loadPolls() {
        // In a real application, load from server
        this.polls = [
            {
                id: 1,
                question: 'What\'s your favorite programming language?',
                options: ['Python', 'JavaScript', 'Java'],
                votes: [45, 35, 20],
                totalVotes: 100,
                createdBy: 'admin',
                createdAt: new Date(Date.now() - 604800000),
                expiresAt: new Date(Date.now() + 604800000)
            },
            {
                id: 2,
                question: 'Best time to schedule meetings?',
                options: ['Morning (9-12 AM)', 'Afternoon (1-5 PM)', 'Evening (5-7 PM)'],
                votes: [60, 30, 10],
                totalVotes: 100,
                createdBy: 'admin',
                createdAt: new Date(Date.now() - 1209600000),
                expiresAt: new Date(Date.now() + 1209600000)
            }
        ];
    }

    getPollPercentage(votes, totalVotes) {
        return totalVotes === 0 ? 0 : Math.round((votes / totalVotes) * 100);
    }

    addPoll(question, options, duration, allowMultiple) {
        const newPoll = {
            id: this.polls.length + 1,
            question: question,
            options: options,
            votes: new Array(options.length).fill(0),
            totalVotes: 0,
            createdBy: 'currentUser',
            createdAt: new Date(),
            expiresAt: new Date(Date.now() + duration * 24 * 60 * 60 * 1000),
            allowMultiple: allowMultiple
        };
        this.polls.push(newPoll);
        return newPoll;
    }

    recordVote(pollId, optionIndex, userId) {
        const poll = this.polls.find(p => p.id === pollId);
        if (!poll) return false;

        const userKey = `${userId}_${pollId}`;
        
        if (!poll.allowMultiple && this.userVotes[userKey]) {
            console.log('User already voted on this poll');
            return false;
        }

        poll.votes[optionIndex]++;
        poll.totalVotes++;
        this.userVotes[userKey] = optionIndex;
        return true;
    }

    getActivePollsCount() {
        const now = new Date();
        return this.polls.filter(p => p.expiresAt > now).length;
    }
}

// Initialize Poll Manager
const pollManager = new PollManager();

// Display stats on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Active Polls:', pollManager.getActivePollsCount());
    console.log('Total Polls:', pollManager.polls.length);
});

// Export for use in other pages
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { PollManager, pollManager };
}

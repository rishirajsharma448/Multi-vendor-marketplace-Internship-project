document.addEventListener('DOMContentLoaded', function() {
    const chatbotToggle = document.getElementById('chatbot-toggle');
    const chatbotWindow = document.getElementById('chatbot-window');
    const chatbotClose = document.getElementById('chatbot-close');
    const chatbotInput = document.getElementById('chatbot-input');
    const chatbotSend = document.getElementById('chatbot-send');
    const chatbotMessages = document.getElementById('chatbot-messages');
    const chatbotChips = document.querySelectorAll('.chatbot-chip');

    // Toggle chatbot window
    if (chatbotToggle && chatbotWindow) {
        chatbotToggle.addEventListener('click', () => {
            chatbotWindow.classList.toggle('open');
            if (chatbotWindow.classList.contains('open')) {
                chatbotInput.focus();
            }
        });
    }

    if (chatbotClose && chatbotWindow) {
        chatbotClose.addEventListener('click', () => {
            chatbotWindow.classList.remove('open');
        });
    }

    // Handle Quick Suggestion Chips
    chatbotChips.forEach(chip => {
        chip.addEventListener('click', function() {
            const msg = this.getAttribute('data-msg') || this.innerText;
            chatbotInput.value = msg;
            sendMessage();
        });
    });

    // Handle sending messages
    function sendMessage() {
        const message = chatbotInput.value.trim();
        if (message === '') return;

        // Append user message
        appendMessage('user', message);
        chatbotInput.value = '';
        chatbotInput.focus();

        // Show typing indicator
        showTypingIndicator();

        // Send to backend
        const endpoint = (window.APP_URL || '') + '/api/chatbot.php';
        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            removeTypingIndicator();
            if (data.error) {
                appendMessage('bot', 'Sorry, I encountered an error: ' + data.error);
            } else {
                appendMessage('bot', data.reply);
            }
        })
        .catch(error => {
            removeTypingIndicator();
            appendMessage('bot', 'I am here to help! Ask me to compare products, check customer reviews, or view product photos.');
            console.error('Chatbot error:', error);
        });
    }

    if (chatbotSend) {
        chatbotSend.addEventListener('click', sendMessage);
    }

    if (chatbotInput) {
        chatbotInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }

    function appendMessage(sender, text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `chatbot-message ${sender}`;
        
        if (sender === 'bot') {
            msgDiv.innerHTML = formatBotMarkdown(text);
        } else {
            msgDiv.textContent = text;
        }

        chatbotMessages.appendChild(msgDiv);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    function formatBotMarkdown(rawText) {
        if (!rawText) return '';

        // 1. Extract and format Markdown Tables
        let text = formatMarkdownTables(rawText);

        // 2. Format Images: ![Alt](url)
        text = text.replace(/!\[(.*?)\]\((.*?)\)/g, function(match, alt, url) {
            // Clean up relative path if needed
            let cleanUrl = url.trim();
            if (!cleanUrl.startsWith('http') && !cleanUrl.startsWith('/')) {
                cleanUrl = (window.APP_URL ? window.APP_URL + '/' : '') + cleanUrl;
            }
            return `<div class="chatbot-product-img-wrapper"><img src="${cleanUrl}" alt="${alt}" class="chatbot-product-img" onerror="this.src='assets/images/default-product.png'" title="${alt}"><small class="text-muted d-block mt-1" style="font-size:10px;">📸 ${alt}</small></div>`;
        });

        // 3. Format Links: [Text](url)
        text = text.replace(/\[(.*?)\]\((.*?)\)/g, function(match, label, url) {
            let cleanUrl = url.trim();
            if (!cleanUrl.startsWith('http') && !cleanUrl.startsWith('/')) {
                cleanUrl = (window.APP_URL ? window.APP_URL + '/' : '') + cleanUrl;
            }
            return `<a href="${cleanUrl}" class="chatbot-action-link" target="_blank">${label} <i class="bi bi-arrow-right-short"></i></a>`;
        });

        // 4. Headers: ### Header, ## Header
        text = text.replace(/^###\s*(.*$)/gim, '<h6 class="chatbot-heading mt-2 mb-1">$1</h6>');
        text = text.replace(/^##\s*(.*$)/gim, '<h6 class="chatbot-heading fw-bold mt-2 mb-1">$1</h6>');

        // 5. Bold: **text**
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // 6. Italic: *text* or _text_
        text = text.replace(/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/g, '<em>$1</em>');

        // 7. Horizontal rule: ---
        text = text.replace(/^---$/gim, '<hr class="my-2 opacity-50">');

        // 8. Bullet points: • or - or *
        text = text.replace(/^[•\-\*]\s+(.*$)/gim, '<li>$1</li>');
        text = text.replace(/(<li>.*?<\/li>)/gs, '<ul class="mb-2 ps-3">$1</ul>');
        // Clean nested consecutive uls
        text = text.replace(/<\/ul>\s*<ul class="mb-2 ps-3">/g, '');

        // 9. Line breaks (preserve newlines that are not inside tables/headings)
        text = text.replace(/\n\n/g, '<br><br>');
        text = text.replace(/(?<!<br>|<\/h6>|<\/div>|<\/table>|<\/ul>)\n/g, '<br>');

        return text;
    }

    /**
     * Parses Markdown table syntax | Col1 | Col2 | into clean HTML <table>
     */
    function formatMarkdownTables(text) {
        const lines = text.split('\n');
        let inTable = false;
        let tableRows = [];
        let newLines = [];

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i].trim();
            if (line.startsWith('|') && line.endsWith('|')) {
                // Table line
                inTable = true;
                // Ignore divider lines like | :--- | :--- |
                if (/^\|(\s*:?-+:?\s*\|)+$/.test(line)) {
                    continue;
                }
                const cells = line.split('|').slice(1, -1).map(c => c.trim());
                tableRows.push(cells);
            } else {
                if (inTable) {
                    newLines.push(renderHtmlTable(tableRows));
                    tableRows = [];
                    inTable = false;
                }
                newLines.push(lines[i]);
            }
        }

        if (inTable && tableRows.length > 0) {
            newLines.push(renderHtmlTable(tableRows));
        }

        return newLines.join('\n');
    }

    function renderHtmlTable(rows) {
        if (!rows || rows.length === 0) return '';
        let html = '<div class="chatbot-table-wrapper"><table class="chatbot-table"><thead><tr>';
        const headerCells = rows[0];
        headerCells.forEach(cell => {
            html += `<th>${cell}</th>`;
        });
        html += '</tr></thead><tbody>';

        for (let r = 1; r < rows.length; r++) {
            html += '<tr>';
            rows[r].forEach(cell => {
                html += `<td>${cell}</td>`;
            });
            html += '</tr>';
        }
        html += '</tbody></table></div>';
        return html;
    }

    function showTypingIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'chatbot-message bot typing';
        indicator.id = 'typing-indicator-msg';
        indicator.innerHTML = `
            <div class="typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        `;
        chatbotMessages.appendChild(indicator);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        if (chatbotSend) chatbotSend.disabled = true;
    }

    function removeTypingIndicator() {
        const indicator = document.getElementById('typing-indicator-msg');
        if (indicator) {
            indicator.remove();
        }
        if (chatbotSend) chatbotSend.disabled = false;
    }
});

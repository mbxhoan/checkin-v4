<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $luckyDraw->name }} - Lucky Draw Display</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #000;
            font-family: 'Arial', sans-serif;
        }
        #display-container {
            width: 100vw;
            height: 100vh;
            position: relative;
        }
        #canvas-container {
            width: 100%;
            height: 100%;
        }
        .control-panel {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .control-panel:hover,
        .control-panel.visible {
            opacity: 1;
        }
        .control-btn {
            padding: 12px 24px;
            font-size: 18px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .control-btn:hover {
            transform: scale(1.05);
        }
        .control-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        .btn-start {
            background: linear-gradient(135deg, #00c853, #00e676);
            color: white;
        }
        .btn-stop {
            background: linear-gradient(135deg, #ff1744, #ff5252);
            color: white;
        }
        .btn-confirm {
            background: linear-gradient(135deg, #2196f3, #42a5f5);
            color: white;
        }
        .btn-reset {
            background: linear-gradient(135deg, #607d8b, #78909c);
            color: white;
        }
        .reward-selector {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .reward-selector:hover,
        .reward-selector.visible {
            opacity: 1;
        }
        .reward-selector select {
            padding: 10px 15px;
            font-size: 16px;
            border-radius: 8px;
            border: none;
            background: rgba(255,255,255,0.9);
        }
        .state-indicator {
            position: fixed;
            top: 20px;
            left: 20px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            z-index: 1000;
        }
        .state-idle { background: #607d8b; color: white; }
        .state-spinning { background: #ff9800; color: white; animation: pulse 1s infinite; }
        .state-result { background: #4caf50; color: white; }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
</head>
<body>
    <div id="display-container"
         data-lucky-draw-id="{{ $luckyDraw->id }}"
         data-api-base="{{ route('admin.lucky_draws.draw.state', $luckyDraw) }}"
         data-draw-base="{{ route('admin.lucky_draws.draw.state', $luckyDraw) }}">

        <div id="canvas-container"></div>

        <div class="state-indicator state-idle" id="state-indicator">Idle</div>

        <div class="reward-selector visible">
            <select id="reward-select">
                <option value="">Select Prize</option>
                @foreach($luckyDraw->rewards()->orderBy('order')->get() as $reward)
                <option value="{{ $reward->id }}" {{ $reward->is_given ? 'disabled' : '' }}>
                    {{ $reward->order_name }} - {{ $reward->name }}
                    {{ $reward->is_given ? '(Done)' : '' }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="control-panel visible" id="control-panel">
            <button class="control-btn btn-start" id="btn-start" disabled>
                <i class="fas fa-play"></i> Quay
            </button>
            <button class="control-btn btn-stop" id="btn-stop" disabled>
                <i class="fas fa-stop"></i> Dừng
            </button>
            <button class="control-btn btn-confirm" id="btn-confirm" disabled title="{{ __('lucky_draws.display.confirm_button_title') }}">
                <i class="fas fa-check"></i> Xác nhận (Lưu)
            </button>
            <button class="control-btn btn-reset" id="btn-reset">
                <i class="fas fa-redo"></i> Đặt lại
            </button>
        </div>
    </div>

    <script src="https://unpkg.com/konva@9/konva.min.js"></script>
    <script>
        const container = document.getElementById('display-container');
        const luckyDrawId = container.dataset.luckyDrawId;
        const apiBase = '{{ route("admin.lucky_draws.draw.state", $luckyDraw) }}'.replace('/state', '');
        const proxyImageBase = '{{ route("admin.lucky_draws.builder.proxy-image", $luckyDraw) }}';

        const layout = @json($layout);
        const clients = @json($clients);
        const rewards = @json($luckyDraw->rewards()->orderBy('order')->get()->map(function($r) {
            return ['id' => $r->id, 'img_link' => $r->img_link, 'layout_id' => $r->layout?->id];
        }));
        const layoutApiBase = '{{ route("admin.lucky_draws.builder.index", $luckyDraw) }}';

        let stage, layer, backgroundLayer;
        let currentState = 'idle';
        let selectedRewardId = null;
        let currentLayout = layout;
        let spinInterval = null;
        let currentClientIndex = 0;
        let blockNodes = {};

        function resolveImageUrl(url) {
            if (!url) return url;
            // Relative URLs are same-origin, no proxy needed
            if (url.startsWith('/')) return url;

            try {
                const parsed = new URL(url, window.location.origin);
                if (parsed.origin !== window.location.origin) {
                    return `${proxyImageBase}?url=${encodeURIComponent(url)}`;
                }
            } catch (e) {
                // If parsing fails, just return raw url
            }

            // Also proxy https images when running local http (avoid mixed/CORS issues)
            if (url.startsWith('https://') && window.location.origin.startsWith('http://')) {
                return `${proxyImageBase}?url=${encodeURIComponent(url)}`;
            }

            return url;
        }

        // Load layout for a reward
        async function loadRewardLayout(rewardId) {
            if (!rewardId) {
                currentLayout = layout;
                initCanvas();
                return;
            }

            const reward = rewards.find(r => r.id == rewardId);
            if (!reward) {
                currentLayout = layout;
                initCanvas();
                return;
            }

            try {
                // Try to load reward's layout
                if (reward.layout_id) {
                    const response = await fetch(`${layoutApiBase}/layouts/${reward.layout_id}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });
                    if (response.ok) {
                        const data = await response.json();
                        currentLayout = data.layout;
                        initCanvas();
                        return;
                    }
                }

                // If no layout, use default but set background from reward image
                currentLayout = { ...layout };
                if (reward.img_link) {
                    currentLayout.background_type = 'image';
                    currentLayout.background_value = reward.img_link;
                }
                initCanvas();
            } catch (error) {
                console.error('Failed to load reward layout:', error);
                // Fallback: use default layout with reward image
                currentLayout = { ...layout };
                if (reward.img_link) {
                    currentLayout.background_type = 'image';
                    currentLayout.background_value = reward.img_link;
                }
                initCanvas();
            }
        }

        // Initialize Konva Stage
        function initCanvas() {
            const canvasContainer = document.getElementById('canvas-container');
            const containerWidth = canvasContainer.offsetWidth;
            const containerHeight = canvasContainer.offsetHeight;

            // Destroy existing stage if exists
            if (stage) {
                stage.destroy();
            }

            const canvasWidth = currentLayout.canvas_width || 1920;
            const canvasHeight = currentLayout.canvas_height || 1080;

            const scaleX = containerWidth / canvasWidth;
            const scaleY = containerHeight / canvasHeight;
            const scale = Math.min(scaleX, scaleY);

            const offsetX = (containerWidth - canvasWidth * scale) / 2;
            const offsetY = (containerHeight - canvasHeight * scale) / 2;

            stage = new Konva.Stage({
                container: 'canvas-container',
                width: containerWidth,
                height: containerHeight,
                scale: { x: scale, y: scale },
                x: offsetX,
                y: offsetY
            });

            backgroundLayer = new Konva.Layer();
            stage.add(backgroundLayer);

            layer = new Konva.Layer();
            stage.add(layer);

            // Background
            const bgRect = new Konva.Rect({
                x: 0,
                y: 0,
                width: canvasWidth,
                height: canvasHeight,
                fill: currentLayout.background_type === 'color' ? (currentLayout.background_value || '#000000') : '#000000'
            });
            backgroundLayer.add(bgRect);

            if (currentLayout.background_type === 'image' && currentLayout.background_value) {
                const imageObj = new Image();
                imageObj.onload = function() {
                    const bgImage = new Konva.Image({
                        x: 0,
                        y: 0,
                        width: canvasWidth,
                        height: canvasHeight,
                        image: imageObj
                    });
                    backgroundLayer.add(bgImage);
                    bgImage.moveToBottom();
                    bgRect.moveToBottom();
                    backgroundLayer.draw();
                    console.log('Background image loaded:', currentLayout.background_value);
                };
                imageObj.onerror = function(error) {
                    console.error('Failed to load background image:', currentLayout.background_value, error);
                    bgRect.fill('#000000');
                    backgroundLayer.draw();
                };
                imageObj.src = resolveImageUrl(currentLayout.background_value);
            }

            // Render blocks
            (currentLayout.blocks || []).forEach(block => {
                const node = createBlockNode(block);
                if (node) {
                    layer.add(node);
                    blockNodes[block.id] = { node, config: block };
                }
            });

            stage.draw();
        }

        function createBlockNode(config) {
            if (['text', 'random_field', 'result_field'].includes(config.type)) {
                return new Konva.Text({
                    x: config.x,
                    y: config.y,
                    width: config.width,
                    height: config.height,
                    text: getBlockText(config),
                    fontSize: config.style?.fontSize || 48,
                    fontFamily: config.style?.fontFamily || 'Arial',
                    fontStyle: config.style?.fontWeight || 'normal',
                    fill: config.style?.color || '#FFFFFF',
                    align: config.style?.align || 'center',
                    verticalAlign: 'middle',
                    rotation: config.rotation || 0
                });
            }

            if (config.type === 'image') {
                const group = new Konva.Group({
                    x: config.x,
                    y: config.y,
                    width: config.width,
                    height: config.height,
                    rotation: config.rotation || 0
                });

                if (config.imageUrl) {
                    const imageObj = new Image();
                    imageObj.onload = function() {
                        const img = new Konva.Image({
                            image: imageObj,
                            width: config.width,
                            height: config.height
                        });
                        group.add(img);
                        layer.draw();
                    };
                    imageObj.src = resolveImageUrl(config.imageUrl);
                }
                return group;
            }

            if (config.type === 'avatar') {
                const group = new Konva.Group({
                    x: config.x,
                    y: config.y,
                    width: config.width,
                    height: config.height,
                    rotation: config.rotation || 0
                });

                const radius = Math.min(config.width, config.height) / 2;
                const circle = new Konva.Circle({
                    x: config.width / 2,
                    y: config.height / 2,
                    radius: radius,
                    fill: '#555',
                    stroke: '#888',
                    strokeWidth: 3
                });
                group.add(circle);

                return group;
            }

            return null;
        }

        function getBlockText(config) {
            if (config.content) return config.content;
            if (config.source) return `[${config.source}]`;
            if (config.type === 'random_field') return '[Spinning...]';
            if (config.type === 'result_field') return '[Winner]';
            return '';
        }

        // Update block visibility based on state
        function updateBlockVisibility(state) {
            Object.values(blockNodes).forEach(({ node, config }) => {
                const visibleWhen = config.visibleWhen || 'always';
                let visible = true;

                // Chỉ có 2 options: 'always' và 'result'
                // 'result' sẽ hiển thị khi state là 'result'
                if (visibleWhen === 'result' && state !== 'result') {
                    visible = false;
                }

                node.visible(visible);
            });
            layer.draw();
        }

        // Spinning animation
        function startSpinning() {
            if (spinInterval) return;

            // Quay tất cả các ô random_field cùng lúc (chỉ khi đang spinning, không ghi đè khi đã result)
            spinInterval = setInterval(() => {
                if (currentState !== 'spinning') return; // Tránh ghi đè sau khi đã dừng ra kết quả
                Object.values(blockNodes).forEach(({ node, config }) => {
                    if (config.type === 'random_field' && node.text) {
                        const randomClient = clients[Math.floor(Math.random() * clients.length)];
                        const fieldKey = config.source || 'name';
                        const value = getFieldValue(randomClient, fieldKey);
                        node.text(value);
                    }
                });
                layer.draw();
            }, 50);
        }

        function stopSpinning() {
            if (spinInterval) {
                clearInterval(spinInterval);
                spinInterval = null;
            }
        }

        function updateRandomFields(client) {
            Object.values(blockNodes).forEach(({ node, config }) => {
                if (config.type === 'random_field' && node.text) {
                    const fieldKey = config.source || 'name';
                    const value = getFieldValue(client, fieldKey);
                    node.text(value);
                }
            });
            layer.draw();
        }

        // Hiển thị nhiều người thắng: mỗi ô quay (random_field) = 1 slot = 1 người khác nhau
        function showWinners(winners) {
            stopSpinning();
            
            if (!Array.isArray(winners) || winners.length === 0) {
                console.warn('No winners to show');
                return;
            }
            
            const allFieldBlocks = Object.values(blockNodes).filter(({ config }) =>
                config.type === 'random_field' || config.type === 'result_field'
            );
            
            // Sắp xếp random_field theo slotIndex rồi theo vị trí (y, x)
            const randomFields = allFieldBlocks
                .filter(({ config }) => config.type === 'random_field')
                .sort((a, b) => {
                    const sa = a.config.slotIndex ?? 0, sb = b.config.slotIndex ?? 0;
                    if (sa !== sb) return sa - sb;
                    return (a.config.y ?? 0) - (b.config.y ?? 0) || (a.config.x ?? 0) - (b.config.x ?? 0);
                });
            
            if (randomFields.length === 0) return;
            
            // Xây từng nhóm: mỗi random_field + result_fields "thuộc" slot đó
            const slotGroups = [];
            
            for (let i = 0; i < randomFields.length; i++) {
                const rf = randomFields[i];
                const rfSlotIndex = rf.config.slotIndex;
                const group = [rf];
                
                const sameSlotResults = allFieldBlocks.filter(
                    ({ config }) => config.type === 'result_field' && config.slotIndex === rfSlotIndex
                );
                // Nếu có nhiều random_field cùng slotIndex → chia result theo thứ tự (mỗi rf lấy phần của mình)
                const resultsForThisSlot = sameSlotResults
                    .sort((a, b) => (a.config.y ?? 0) - (b.config.y ?? 0) || (a.config.x ?? 0) - (b.config.x ?? 0));
                
                const rfWithSameSlot = randomFields.filter(r => r.config.slotIndex === rfSlotIndex);
                const idxInSlot = rfWithSameSlot.indexOf(rf);
                const countInSlot = rfWithSameSlot.length;
                const chunkSize = countInSlot ? Math.ceil(resultsForThisSlot.length / countInSlot) : 0;
                const start = idxInSlot * chunkSize;
                const chunk = resultsForThisSlot.slice(start, start + chunkSize);
                chunk.forEach(r => group.push(r));
                slotGroups.push(group);
            }
            
            // Gán winner[i] cho slot thứ i — cập nhật tất cả cùng lúc để không bị ghi đè bởi interval hay logic khác
            slotGroups.forEach((group, displayIndex) => {
                const winner = winners[displayIndex];
                if (!winner) return;
                
                group.forEach(({ node, config }) => {
                    if (node.text) {
                        const fieldKey = config.source || 'name';
                        const value = getFieldValue(winner, fieldKey);
                        node.text(value);
                    }
                });
            });
            layer.draw();
        }

        function updateResultFields(client) {
            Object.values(blockNodes).forEach(({ node, config }) => {
                if (config.type === 'result_field' && node.text) {
                    const fieldKey = config.source || 'name';
                    const value = getFieldValue(client, fieldKey);
                    node.text(value);
                }
            });
            layer.draw();
        }

        function getFieldValue(client, fieldKey) {
            if (!client) return '';

            // Trường custom có key dạng "custom_fields.department" -> lấy client.custom_fields['department']
            if (fieldKey && fieldKey.startsWith('custom_fields.')) {
                const customKey = fieldKey.replace('custom_fields.', '');
                if (client.custom_fields && client.custom_fields[customKey] !== undefined && client.custom_fields[customKey] !== null) {
                    const val = client.custom_fields[customKey];
                    return Array.isArray(val) ? val.join(', ') : String(val);
                }
                return '';
            }

            if (client[fieldKey] !== undefined && client[fieldKey] !== null) {
                return String(client[fieldKey]);
            }
            if (client.custom_fields && client.custom_fields[fieldKey] !== undefined && client.custom_fields[fieldKey] !== null) {
                const val = client.custom_fields[fieldKey];
                return Array.isArray(val) ? val.join(', ') : String(val);
            }
            return client.name || '';
        }

        // State Management
        function updateState(newState, data = {}) {
            // Dừng quay ngay khi chuyển sang result, trước mọi thao tác khác (tránh interval chạy thêm 1 nhịp ghi đè)
            if (newState === 'result') {
                stopSpinning();
            }
            currentState = newState;

            const indicator = document.getElementById('state-indicator');
            indicator.className = 'state-indicator state-' + newState;
            indicator.textContent = newState.charAt(0).toUpperCase() + newState.slice(1);

            document.getElementById('btn-start').disabled = newState !== 'idle' || !selectedRewardId;
            document.getElementById('btn-stop').disabled = newState !== 'spinning';
            // Cho phép nhấn Xác nhận khi có kết quả
            document.getElementById('btn-confirm').disabled = newState !== 'result';

            updateBlockVisibility(newState);

            if (newState === 'spinning') {
                startSpinning();
            } else if (newState === 'result') {
                stopSpinning();
                
                // Xử lý nhiều winners
                if (data.winners && Array.isArray(data.winners)) {
                    showWinners(data.winners);
                } else if (data.winner) {
                    // Fallback cho 1 winner
                    showWinners([data.winner]);
                } else if (data.current_client) {
                    showWinners([data.current_client]);
                }
            } else if (newState === 'idle') {
                stopSpinning();
            }
        }

        // API calls
        async function apiCall(endpoint, method = 'GET', data = null) {
            const options = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            };

            if (data) {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(apiBase + endpoint, options);
            return response.json();
        }

        async function fetchState() {
            try {
                const result = await apiCall('/state');
                if (result.state) {
                    const serverState = result.state.state || 'idle';
                    // Đang hiển thị kết quả: không ghi đè bằng idle/spinning, và không gọi lại updateState('result') để tránh showWinners chạy 2 lần (nhảy thêm người)
                    if (currentState === 'result') {
                        if (serverState === 'idle' || serverState === 'spinning') {
                            return; // Giữ nguyên màn hình kết quả để user kịp nhấn Xác nhận
                        }
                        if (serverState === 'result') {
                            return; // Đã hiển thị result rồi, không gọi updateState/showWinners lại
                        }
                    }
                    updateState(serverState, result.state);

                    // Keep reward selector in sync and load layout
                    if (result.state.current_reward_id) {
                        const newRewardId = String(result.state.current_reward_id);
                        if (selectedRewardId !== newRewardId) {
                            selectedRewardId = newRewardId;
                            const select = document.getElementById('reward-select');
                            if (select && select.value !== selectedRewardId) {
                                select.value = selectedRewardId;
                            }
                            await loadRewardLayout(selectedRewardId);
                        }
                    }
                }
            } catch (error) {
                console.error('Failed to fetch state:', error);
            }
        }

        // Event Listeners
        document.getElementById('reward-select').addEventListener('change', async (e) => {
            selectedRewardId = e.target.value;
            document.getElementById('btn-start').disabled = !selectedRewardId || currentState !== 'idle';
            
            // Load layout for selected reward
            if (selectedRewardId) {
                await loadRewardLayout(selectedRewardId);
            }
        });

        document.getElementById('btn-start').addEventListener('click', async () => {
            if (!selectedRewardId) return;

            try {
                const result = await apiCall('/start', 'POST', { reward_id: selectedRewardId });
                if (result.state) {
                    updateState(result.state.state || 'spinning', result.state);
                }
            } catch (error) {
                console.error('Failed to start:', error);
            }
        });

        document.getElementById('btn-stop').addEventListener('click', async () => {
            try {
                const result = await apiCall('/stop', 'POST');
                if (result.state) {
                    // Dừng ngay lập tức và hiển thị kết quả luôn, không cần animation slowing
                    stopSpinning();
                    updateState('result', result.state);
                }
            } catch (error) {
                console.error('Failed to stop:', error);
            }
        });

        document.getElementById('btn-confirm').addEventListener('click', async () => {
            try {
                const result = await apiCall('/confirm', 'POST');
                if (result.state) {
                    updateState('idle', result.state);
                    // Reload page to update reward list
                    location.reload();
                }
            } catch (error) {
                console.error('Failed to confirm:', error);
            }
        });

        document.getElementById('btn-reset').addEventListener('click', async () => {
            try {
                const result = await apiCall('/reset', 'POST');
                if (result.state) {
                    updateState('idle', result.state);
                }
            } catch (error) {
                console.error('Failed to reset:', error);
            }
        });

        // Window resize
        window.addEventListener('resize', () => {
            if (stage) {
                stage.destroy();
            }
            initCanvas();
        });

        // Initialize
        initCanvas();
        fetchState();
        
        // Load layout if reward is pre-selected from state
        if (selectedRewardId) {
            loadRewardLayout(selectedRewardId);
        }

        // Poll for state updates (can be replaced with WebSocket)
        setInterval(fetchState, 2000);
    </script>
</body>
</html>

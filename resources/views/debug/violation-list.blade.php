<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Violation List Debugger</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin-top: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .status-badge {
            font-size: 1.2rem;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
        }
        .status-success {
            background-color: #28a745;
            color: white;
        }
        .status-danger {
            background-color: #dc3545;
            color: white;
        }
        .status-warning {
            background-color: #ffc107;
            color: black;
        }
        .info-row {
            padding: 12px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #333;
            min-width: 200px;
        }
        .info-value {
            color: #666;
            text-align: right;
        }
        .violation-table {
            font-size: 0.9rem;
        }
        .violation-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        .severity-minor {
            background-color: #d4edda;
            color: #155724;
        }
        .severity-major {
            background-color: #fff3cd;
            color: #856404;
        }
        .severity-severe {
            background-color: #f8d7da;
            color: #721c24;
        }
        .action-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-copy {
            background-color: #17a2b8;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-copy:hover {
            background-color: #138496;
        }
        .code-block {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            overflow-x: auto;
            margin-top: 10px;
        }
        .header-title {
            color: white;
            margin-bottom: 30px;
            text-align: center;
        }
        .header-title h1 {
            font-size: 2.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-title">
            <h1>🔍 Violation List Debugger</h1>
            <p class="text-white">Check if violation list data is properly seeded in the database</p>
        </div>

        <!-- Status Overview -->
        <div class="card">
            <div class="card-header">
                <i class="ri-dashboard-line"></i> Status Overview
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <span class="info-label">Database Connection:</span>
                            <span class="info-value">
                                @if($debug['database_connected'])
                                    <span class="status-badge status-success">✓ Connected</span>
                                @else
                                    <span class="status-badge status-danger">✗ Failed</span>
                                @endif
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Database Name:</span>
                            <span class="info-value"><code>{{ $debug['database_name'] ?? 'N/A' }}</code></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Table Exists:</span>
                            <span class="info-value">
                                @if($debug['table_exists'])
                                    <span class="status-badge status-success">✓ Yes</span>
                                @else
                                    <span class="status-badge status-danger">✗ No</span>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <span class="info-label">Violation Records:</span>
                            <span class="info-value"><strong style="font-size: 1.3rem;">{{ $debug['violation_count'] }}</strong></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Seeder Status:</span>
                            <span class="info-value">
                                @if($debug['violation_count'] > 0)
                                    <span class="status-badge status-success">✓ SEEDED</span>
                                @else
                                    <span class="status-badge status-danger">✗ NOT SEEDED</span>
                                @endif
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Expected Records:</span>
                            <span class="info-value"><strong>68</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Environment Info -->
        <div class="card">
            <div class="card-header">
                <i class="ri-server-line"></i> Environment Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <span class="info-label">Environment:</span>
                            <span class="info-value"><code>{{ $debug['environment']['app_env'] }}</code></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Debug Mode:</span>
                            <span class="info-value"><code>{{ $debug['environment']['app_debug'] }}</code></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <span class="info-label">Database Driver:</span>
                            <span class="info-value"><code>{{ $debug['environment']['db_driver'] }}</code></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Database Host:</span>
                            <span class="info-value"><code>{{ $debug['environment']['db_host'] }}</code></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Violations List -->
        @if($debug['violation_count'] > 0)
            <div class="card">
                <div class="card-header">
                    <i class="ri-list-check"></i> Violation List ({{ $debug['violation_count'] }} records)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover violation-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Severity</th>
                                    <th>Category</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($debug['violations'] as $violation)
                                    <tr>
                                        <td><strong>#{{ $violation->id }}</strong></td>
                                        <td>{{ $violation->title }}</td>
                                        <td>
                                            <span class="badge severity-{{ strtolower($violation->severity) }}">
                                                {{ ucfirst($violation->severity) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($violation->category)
                                                <span class="badge bg-info">Category {{ $violation->category }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <i class="ri-alert-line"></i> ⚠️ No Violations Found
                </div>
                <div class="card-body">
                    <p class="text-danger mb-0">
                        <strong>The violation_lists table is empty!</strong><br>
                        The seeder has not been run on this database. Run the following command on your production server:
                    </p>
                    <div class="code-block">
php artisan db:seed --class=ViolationListSeeder
                    </div>
                </div>
            </div>
        @endif

        <!-- API Test -->
        <div class="card">
            <div class="card-header">
                <i class="ri-api-line"></i> API Endpoint Test
            </div>
            <div class="card-body">
                <p>Test the API endpoint that the modal uses to fetch violations:</p>
                <button class="btn btn-primary" onclick="testApi()">
                    <i class="ri-play-line"></i> Test API Endpoint
                </button>
                <div id="api-result" style="margin-top: 15px;"></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <i class="ri-tools-line"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="action-buttons">
                    <button class="btn btn-copy" onclick="copyCommand()">
                        <i class="ri-file-copy-line"></i> Copy Seeder Command
                    </button>
                    <a href="/" class="btn btn-primary">
                        <i class="ri-home-line"></i> Back to Home
                    </a>
                    <button class="btn btn-warning" onclick="location.reload()">
                        <i class="ri-refresh-line"></i> Refresh Page
                    </button>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="card">
            <div class="card-header">
                <i class="ri-information-line"></i> Instructions
            </div>
            <div class="card-body">
                <h5>If violations are NOT showing:</h5>
                <ol>
                    <li>SSH into your production server</li>
                    <li>Navigate to your Laravel application directory</li>
                    <li>Run: <code>php artisan db:seed --class=ViolationListSeeder</code></li>
                    <li>Refresh this page to verify the data is now present</li>
                </ol>
                <hr>
                <h5>To delete this debugger later:</h5>
                <ol>
                    <li>Delete this file: <code>app/Http/Controllers/DebugController.php</code></li>
                    <li>Delete this file: <code>resources/views/debug/violation-list.blade.php</code></li>
                    <li>Remove the debug routes from <code>routes/web.php</code></li>
                </ol>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function testApi() {
            const resultDiv = document.getElementById('api-result');
            resultDiv.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';

            fetch('/debug/test-violation-api')
                .then(response => response.json())
                .then(data => {
                    let html = '<div class="code-block">' + JSON.stringify(data, null, 2) + '</div>';
                    if (data.success) {
                        html = '<div class="alert alert-success">✓ API is working! Found ' + data.count + ' violation types.</div>' + html;
                    } else {
                        html = '<div class="alert alert-danger">✗ API Error: ' + data.error + '</div>' + html;
                    }
                    resultDiv.innerHTML = html;
                })
                .catch(error => {
                    resultDiv.innerHTML = '<div class="alert alert-danger">✗ Error: ' + error.message + '</div>';
                });
        }

        function copyCommand() {
            const command = 'php artisan db:seed --class=ViolationListSeeder';
            navigator.clipboard.writeText(command).then(() => {
                alert('Command copied to clipboard:\n\n' + command);
            });
        }
    </script>
</body>
</html>

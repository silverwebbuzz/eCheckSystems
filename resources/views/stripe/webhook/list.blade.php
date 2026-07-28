<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Webhooks</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css'>
    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css"> -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <div class="container py-4">
        <div class="row">
            <div class="col">
                <h1>Webhooks</h1>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
                <table class="table" id="webhooks-table">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">URL</th>
                            <th scope="col">ENDPOINT ID</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.js"></script>
    <script>
        $(document).ready(function() {
            $('#webhooks-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('stripe.webhook.get') }}",
                ordering: false,
                pageLength: 10, 
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'url', name: 'url' },
                     { data: 'endpoint_id', name: 'endpoint_id' },
                ]
            });
        })
    </script>    
</body>
</html>
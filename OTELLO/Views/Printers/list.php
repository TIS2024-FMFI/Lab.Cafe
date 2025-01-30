<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage 3D Printers</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Manage 3D Printers</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-info">
                        <div class="card-header">
            				<div class="d-inline-block">
					            <h3 class="card-title"><i class="fa fa-list"></i>&nbsp; List of 3D printers</h3>
				            </div>
							<div class="d-inline-block float-right">
                                <a href="<?= base_url('admin/printers/add'); ?>" class="btn btn-success"><i class="fa fa-plus"></i>Add printer</a>
				            </div>
                        </div>
                        <div class="card-body">

                            

                            <!-- Display the list of printers here -->
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>State</th>
                                        <th>Description</th>
                                        <th>Address</th>
                                        <th>API Key</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($printers as $printer): ?>
                                        <tr>
                                            <td><?php echo $printer->name; ?></td>
                                            <script>
                                                function fetchPrinterState(printerId, element) {
                                                    $.ajax({
                                                        url: '<?= base_url("admin/printers/get_state/"); ?>' + printerId,
                                                        method: 'GET',
                                                        success: function(response) {
                                                            element.find('.state-text').text(response.state);
                                                            var stateIndicator = element.find('.state-indicator');
                                                            if (response.state === 'Printing' || response.state === 'Operational') {
                                                                stateIndicator.css('background-color', 'green');
                                                            } else if (response.state === 'Paused') {
                                                                stateIndicator.css('background-color', 'yellow');
                                                            } else {
                                                                stateIndicator.css('background-color', 'red');
                                                            }
                                                        }
                                                    });
                                                }

                                                $(document).ready(function() {
                                                    $('td[data-printer-id]').each(function() {
                                                        var printerId = $(this).data('printer-id');
                                                        fetchPrinterState(printerId, $(this));
                                                    });

                                                    setInterval(function() {
                                                        $('td[data-printer-id]').each(function() {
                                                            var printerId = $(this).data('printer-id');
                                                            fetchPrinterState(printerId, $(this));
                                                        });
                                                    }, 5000);
                                                });
                                            </script>
                                            <td data-printer-id="<?php echo $printer->id; ?>">
                                                <span class="state-indicator" style="display: inline-block; 
                                                width: 10px; height: 10px; border-radius: 50%; background-color: white;"></span>
                                                <span class="state-text"><?php echo $printer->state; ?></span>
                                            </td>
                                            <td><?php echo $printer->description; ?></td>
                                            <td><?php echo $printer->address; ?></td>
                                            <td><?php echo $printer->api_key; ?></td>
                                            <td>
                                                <!-- Add edit and delete buttons here -->
                                                <a href="<?= base_url("admin/printers/print_jobs_list/".$printer->id); ?>" class="btn btn-warning btn-xs mr5" >
                                                <i class="fa fa-list-alt"></i>
                                                </a>

                                                <a href="<?= base_url("admin/printers/edit/".$printer->id); ?>" class="btn btn-warning btn-xs mr5" >
                                                <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="<?= base_url("admin/printers/delete/".$printer->id); ?>" onclick="return confirm('are you sure to delete?')" class="btn btn-danger btn-xs"><i class="fa fa-remove"></i></a> 

                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

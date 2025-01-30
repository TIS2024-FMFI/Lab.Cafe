<!-- application/views/print_logs.php -->
<style>
  /* Style for the question mark icon */
  .question-mark {
    position: relative;
    display: inline-block;
    cursor: pointer;
  }

  /* Style for the tooltip */
  .tooltip {
    visibility: hidden;
    width: 120px;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: 5px;
    padding: 5px;
    position: absolute;
    z-index: 1;
    bottom: 125%;
    left: 50%;
    transform: translateX(-50%);
    opacity: 0;
    transition: opacity 0.3s;
  }

  /* Show the tooltip on hover */
  .question-mark:hover .tooltip {
    visibility: visible;
    opacity: 1;
  }
</style>


<div class="content-wrapper">
    <section class="content-header">
        <h1>Print Logs</h1>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Print Job Logs / <?= $filename; ?> / <a href="<?= base_url('admin/printers/print_jobs_list/'.$printer_id); ?>">Back to printer jobs</a></h3>

                        </div>
                        <div class="box-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Log ID</th>
                                        <th>Job ID</th>
                                        <th>Printer</th>
                                        <th>Progress</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($print_logs as $log): ?>
                                        <tr>
                                            <td><?= $log['id']; ?></td>
                                            <td><?= $log['printjob_id'].': '.$log['printjob_filename']; ?></td>
                                            <td><?= $log['printer_name']; ?></td>
                                            <td>
                                                <?php
                                                $logData = json_decode($log['log_data'], true);
                                                $completion = 'NOT FOUND';

                                                if (isset($logData['extra'])) {
                                                    $extraData = json_decode($logData['extra'], true);
                                                    if (isset($extraData['progress']['completion'])) {
                                                        $completion = $logData['topic'] . "... " . number_format($extraData['progress']['completion'], 2) . '%';
                                                    }
                                                }
                                                echo $completion;
                                                ?>
                                            </td>
                                            <td><?= $log['log_timestamp']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php
$server = new swoole_server("127.0.0.1", 9502);
$server->set(array('task_worker_num' => 4));

$server->on('receive', function($server, $fd, $reactor_id, $data) {
    //收到数据,创建task任务.
    //不要在这里做数据逻辑处理
    //这里的task_id是立即返回的
    $task_id = $server->task($fd);
    echo "Dispath AsyncTask: [id=$task_id]\n";
    //将task_id立即反给
    $server->send($fd, "task_id: {$task_id}");
});

$server->on('task', function ($server, $task_id, $reactor_id, $data) {
    echo "New AsyncTask[id=$task_id]\n";
    //这里写数据处理逻辑.
    sleep(20);
    //完成后将结果传给finish
    $server->finish($data);
});

$server->on('finish', function ($server, $task_id, $data) {
    echo "AsyncTask[$task_id] finished: {$data}\n";
    //我这里的send只是演示把数据再次给客户端.实际中可采用别的方式
    $server->send($data, "task_id: {$task_id}");
});

$server->start();
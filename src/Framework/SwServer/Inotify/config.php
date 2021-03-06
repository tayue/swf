<?php

return [
	'afterNSeconds' => 3,
	'isOnline' => false,
	'monitorPort' => 9502,
	'monitorPath' => '/home/www/xxxxx',
	'logFilePath' => __DIR__.'/xxxx.log',
	'monitorProcessName' => 'php-autoreload-swoole-server',
	'reloadFileTypes' => ['.php','.html','.js'],
	'smtpTransport' => [
		"server_host"=>"smtp.163.com",
		"port"      =>25,
		"security"  =>null,
		"user_name" =>"xxxx@163.com",
		"pass_word" =>"XXXXXX"
	],

	'message' => [
		//邮箱主题
		"subject"=>"test",
		//发送者邮箱与定义的名称，邮箱与上面定义的user_name这里必须一致
		"from"   =>["xxxxx@163.com"=>"tayueliuxiang"],
		//定义多个收件人和对应的名称
		"to"     =>['xxxxxx@qq.com'=>"tayueliuxiang", "xxxxx@gmail.com"=>"xxxx"],
		//定义邮件的内容，格式可以包含html
		"body"   =>"<p>this is a mail</p>",
		// body文档类型
		"mime"   =>"text/html",
		//定义要上传的附件，可以多个，附件的大小，由代理的邮件服务器定义提供,key值代表是文件路径，name值代表是发送后的文件显示的别名，如果没设置name值，则以原文件名作为别名
		// 	"attach" =>["/home/wwwroot/default/swoolefy/score/Test/test.docx"=>"my.docx","/home/wwwroot/default/swoolefy/score/Test/test.log","/home/wwwroot/default/swoolefy/score/Test/test.log"=>"my.log"],
		// ];
	]

];
<?php

// +----------------------------------------------------------------------
// | 日志设置
// +----------------------------------------------------------------------
return [
    // 默认日志记录通道（保持文件驱动）
    'default'      => 'file',
    // 日志记录级别（增加 error、debug、sql 级别）
    'level'        => ['error', 'info', 'debug', 'sql'],
    // 日志类型通道映射（暂时保留空）
    'type_channel' => [],
    // 关闭全局日志写入（保持关闭）
    'close'        => false,
    // 日志处理器（保持默认）
    'processor'    => null,

    // 日志通道列表
    'channels'     => [
        'file' => [
            // 驱动类型（文件日志）
            'type'           => 'File',
            // 日志存储目录（修复路径）
            'path'           => runtime_path('log') . DIRECTORY_SEPARATOR,
            // 单文件日志（关闭，按日期分割）
            'single'         => false,
            // 独立日志级别（error 单独存储）
            'apart_level'    => ['error'],
            // 最大日志文件数（30天自动清理）
            'max_files'      => 30,
            // JSON格式记录（保持关闭）
            'json'           => false,
            // 日志处理器（保持默认）
            'processor'      => null,
            // 关闭通道写入（保持开启）
            'close'          => false,
            // 日志格式（保持默认）
            'format'         => '[%s][%s] %s',
            // 实时写入（建议开启）
            'realtime_write' => true,
        ],
        // 其他通道（如需要可添加）
    ],
];

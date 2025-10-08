<div class="flex items-center space-x-3">
    <div class="flex-shrink-0 h-10 w-10">
        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
            <span class="text-sm font-medium text-gray-700">
                {{ substr($row->name, 0, 2) }}
            </span>
        </div>
    </div>
    <div>
        <div class="text-sm font-medium text-gray-900">{{ $row->name }}</div>
        <div class="text-sm text-gray-500">{{ $row->email }}</div>
    </div>
</div>
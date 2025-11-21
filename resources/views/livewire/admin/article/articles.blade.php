<!-- Modern Articles Dashboard -->
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-blue-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Document Library</h1>
                    <p class="text-gray-600">Manage and organize your knowledge base documents</p>
                </div>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" placeholder="Search documents..." 
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <select class="border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option>All Types</option>
                        <option>PDF</option>
                        <option>DOC</option>
                        <option>TXT</option>
                    </select>
                    
                    <select class="border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option>All Status</option>
                        <option>Processed</option>
                        <option>Processing</option>
                        <option>Failed</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Documents Table -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Documents</h3>
            </div>
            
            <div class="overflow-x-auto">
                <livewire:tables.article-table />
            </div>
        </div>
    </div>
</div>

@script
    <script>
        jQuery(document).on('click', '.delete-article', async function() {
            let { id = '', name = '' } = this.dataset;

            if(id) {
                var checkAlert = await fireConfirmationAlert(`Are you sure you want to delete article ${name}?`, 'error');

                if (checkAlert) {
                    var _event = `delete-article`;

                    Livewire.dispatch(_event, { id: id });
                }
            }
        });
    </script>
@endscript
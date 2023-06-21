<x-pulse::card class="col-span-3">
    <x-slot:title>
        <x-pulse::card-title class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6 mr-2 stroke-gray-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>
                <span title="Time: {{ $time }}ms; Run at: {{ $runAt }}">Slow Jobs</span>
                <small class="ml-2 text-gray-400 text-xs font-medium">past {{ match ($this->period) {
                    '6_hours' => '6 hours',
                    '24_hours' => '24 hours',
                    '7_days' => '7 days',
                    default => 'hour',
                } }}, &gt;&equals;{{ config('pulse.slow_jobs_threshold') }}ms</small>
            </span>
        </x-pulse::card-title>
    </x-slot:title>

    <div class="max-h-56 h-full relative overflow-y-auto" wire:poll.5s>
        <script>
            const initialSlowJobsDataLoaded = @js($initialDataLoaded)
        </script>
        <div x-data="{
            initialDataLoaded: initialSlowJobsDataLoaded,
            loadingNewDataset: false,
            init() {
                Livewire.on('periodChanged', () => (this.loadingNewDataset = true))

                window.addEventListener('slow-jobs:dataLoaded', () => {
                    this.initialDataLoaded = true
                    this.loadingNewDataset = false
                })

                if (! this.initialDataLoaded) {
                    @this.loadData()
                }
            }
        }">
            <x-pulse::loading-indicator x-cloak x-show="! initialDataLoaded"/>
            <div x-cloak x-show="initialDataLoaded">
                <div :class="loadingNewDataset ? 'opacity-25 animate-pulse' : ''">
                    @if ($initialDataLoaded && count($slowJobs) === 0)
                        <x-pulse::no-results />
                    @elseif ($initialDataLoaded && count($slowJobs) > 0)
                        <x-pulse::table class="table-fixed">
                            <x-pulse::thead>
                                <tr>
                                    <x-pulse::th class="text-left">Job</x-pulse::th>
                                    <x-pulse::th class="text-right w-24">Count</x-pulse::th>
                                    <x-pulse::th class="text-right w-24">Slowest</x-pulse::th>
                                </tr>
                            </x-pulse::thead>
                            <tbody>
                                @foreach ($slowJobs as $job)
                                    <tr>
                                        <x-pulse::td>
                                            <code class="block text-xs text-gray-900 truncate" title="{{ $job->job }}">
                                                {{ $job->job }}
                                            </code>
                                        </x-pulse::td>
                                        <x-pulse::td class="text-right text-gray-700 text-sm w-24">
                                            <strong>{{ $job->count }}</strong>
                                        </x-pulse::td>
                                        <x-pulse::td class="text-right text-gray-700 text-sm w-24 whitespace-nowrap">
                                            @if ($job->slowest === null)
                                                <strong>Unknown</strong>
                                            @else
                                                <strong>{{ $job->slowest ?: '<1' }}</strong> ms
                                            @endif
                                        </x-pulse::td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </x-pulse::table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-pulse::card>
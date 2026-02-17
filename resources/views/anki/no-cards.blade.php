@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4">
    <div class="max-w-2xl mx-auto">
        <a href="{{ route('anki.index') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold mb-8 inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Voltar ao Dashboard
        </a>

        <div class="bg-white rounded-lg shadow-lg p-12 text-center">
            <svg class="w-20 h-20 text-gray-400 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            
            <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ $deck->name }}</h2>
            <p class="text-gray-600 mb-8 text-lg">
                Não há cards prontos para estudar agora. Volte em breve para revisar!
            </p>

            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6 mb-8">
                <p class="text-gray-700">
                    <span class="font-semibold">Total de Cards:</span> {{ $deck->total_cards }}
                </p>
            </div>

            <div class="flex gap-4 justify-center">
                <a href="{{ route('anki.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                    Voltar ao Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

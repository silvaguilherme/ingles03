@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <a href="{{ route('courses.show', $course) }}" class="text-sm text-indigo-600 hover:text-indigo-500 mb-2 inline-block">
                        ← {{ $course->title }}
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $subModule->title }}</h1>
                    @if($subModule->description)
                        <p class="text-gray-600 mt-2">{{ $subModule->description }}</p>
                    @endif
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('submodules.edit', $subModule) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        ✏️ Editar
                    </a>
                    <form method="POST" action="{{ route('submodules.destroy', $subModule) }}" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700" onclick="return confirm('Tem certeza que deseja deletar este submódulo?')">
                            🗑️ Deletar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Lessons -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">📚 Aulas</h2>
                        <a href="{{ route('lessons.create', $subModule) }}" class="px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                            ➕ Adicionar Aula
                        </a>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @forelse($subModule->lessons as $lesson)
                            <div class="px-6 py-4 hover:bg-gray-50 transition flex items-center justify-between">
                                <div class="flex-1">
                                    <a href="{{ route('lessons.show', $lesson) }}" class="text-indigo-600 hover:text-indigo-500 font-medium">
                                        {{ $lesson->title }}
                                    </a>
                                    @if($lesson->description)
                                        <p class="text-gray-600 text-sm mt-1">{{ $lesson->description }}</p>
                                    @endif
                                </div>
                                <div class="flex gap-2 ml-4">
                                    <a href="{{ route('lessons.edit', $lesson) }}" class="text-xs bg-blue-500 text-white px-2 py-1 rounded">✏️</a>
                                    <form method="POST" action="{{ route('lessons.destroy', $lesson) }}" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs bg-red-500 text-white px-2 py-1 rounded" onclick="return confirm('Tem certeza?')">🗑️</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-8 text-center text-gray-500">
                                <p>Nenhuma aula cadastrada</p>
                                <a href="{{ route('lessons.create', $subModule) }}" class="text-indigo-600 hover:text-indigo-500 text-sm mt-2 inline-block">
                                    Criar primeira aula →
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Anki Decks -->
            <div>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">🎴 Decks Anki</h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @forelse($subModule->ankiDecks as $deck)
                            <div class="px-6 py-4 hover:bg-gray-50 transition">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <a href="{{ route('anki.study', $deck) }}" class="text-indigo-600 hover:text-indigo-500 font-medium block mb-1">
                                            {{ $deck->name }}
                                        </a>
                                        <p class="text-gray-600 text-sm">
                                            {{ $deck->cards()->count() }} cards
                                        </p>
                                    </div>
                                    <form method="POST" action="{{ route('anki-decks.destroy', $deck) }}" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs bg-red-500 text-white px-2 py-1 rounded" onclick="return confirm('Tem certeza?')">🗑️</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-8 text-center text-gray-500">
                                <p class="text-sm">Nenhum deck</p>
                            </div>
                        @endforelse

                        <div class="px-6 py-3 bg-gray-50 border-t">
                            <a href="{{ route('anki-decks.create', $subModule) }}" class="w-full block bg-purple-600 text-white text-center py-2 rounded-lg hover:bg-purple-700 text-sm font-medium">
                                ➕ Novo Deck
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="mt-6 bg-blue-50 rounded-lg shadow p-6 border border-blue-200">
                    <h3 class="font-semibold text-gray-900 mb-3">📊 Resumo</h3>
                    <div class="space-y-2 text-sm">
                        <p><span class="text-gray-600">Aulas:</span> <span class="font-semibold">{{ $subModule->lessons()->count() }}</span></p>
                        <p><span class="text-gray-600">Decks:</span> <span class="font-semibold">{{ $subModule->ankiDecks()->count() }}</span></p>
                        <p><span class="text-gray-600">Cards:</span> <span class="font-semibold">{{ $subModule->ankiDecks()->with('cards')->get()->sum(fn($d) => $d->cards()->count()) }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

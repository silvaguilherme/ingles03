@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('anki.index') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold mb-4 inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Voltar ao Dashboard
            </a>
            <h1 class="text-4xl font-bold text-gray-900">{{ $deck->name }}</h1>
            <p class="text-gray-600 mt-2">{{ $deck->submodule->title }}</p>
        </div>

        <!-- Card de Estudo -->
        <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
            <div id="study-container">
                <!-- Card atual será carregado aqui via JavaScript -->
                <div class="p-12 text-center">
                    <div class="spinner border border-indigo-200 rounded-full w-12 h-12 border-t-indigo-600 mx-auto animate-spin"></div>
                    <p class="text-gray-600 mt-4">Carregando card...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para gerenciar o estudo -->
<script>
const cards = @json($cards);
let currentIndex = 0;
let isFlipped = false;

function renderCard() {
    if (currentIndex >= cards.length) {
        document.getElementById('study-container').innerHTML = `
            <div class="p-12 text-center">
                <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Parabéns!</h2>
                <p class="text-gray-600 mb-6">Você completou todos os cards prontos para revisão.</p>
                <a href="{{ route('anki.index') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                    Voltar ao Dashboard
                </a>
            </div>
        `;
        return;
    }

    const card = cards[currentIndex];
    const progress = (currentIndex + 1) / cards.length * 100;

    let html = `
        <div class="bg-gradient-to-r from-indigo-500 to-blue-600 px-6 py-3 text-white flex justify-between items-center">
            <span class="font-semibold">Card ${currentIndex + 1} de ${cards.length}</span>
            <span class="text-sm opacity-80">${progress.toFixed(0)}%</span>
        </div>

        <div class="p-12">
            <!-- Barra de progresso -->
            <div class="w-full bg-gray-200 rounded-full h-2 mb-8">
                <div class="bg-indigo-600 h-2 rounded-full transition-all" style="width: ${progress}%"></div>
            </div>

            <!-- Card -->
            <div class="mb-8">
                <div id="card-flip" class="cursor-pointer perspective">
                    <div class="relative w-full h-64 transition-transform duration-500 transform" style="transform-style: preserve-3d;" id="card-inner">
                        <!-- Front -->
                        <div class="absolute w-full h-full bg-gradient-to-br from-indigo-50 to-blue-50 rounded-lg p-8 border-2 border-indigo-200 flex flex-col items-center justify-center" id="card-front" style="backface-visibility: hidden;">
                            <p class="text-center text-gray-500 text-sm mb-2">PERGUNTA</p>
                            <h3 class="text-3xl font-bold text-gray-900 text-center">${card.front}</h3>
                            <p class="text-gray-400 text-sm mt-8">Clique para virar o card</p>
                        </div>

                        <!-- Back -->
                        <div class="absolute w-full h-full bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-8 border-2 border-green-200 flex flex-col items-center justify-center" id="card-back" style="backface-visibility: hidden; transform: rotateY(180deg);">
                            <p class="text-center text-gray-500 text-sm mb-2">RESPOSTA</p>
                            <div class="text-lg text-gray-900 text-center">${card.back}</div>
                            ${card.extra ? `<p class="text-gray-600 text-sm mt-4 italic">${card.extra}</p>` : ''}
                            <p class="text-gray-400 text-sm mt-8">Clique para virar o card</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tags -->
            ${card.tags ? `
                <div class="mb-8 flex flex-wrap gap-2 justify-center">
                    ${card.tags.split(' ').map(tag => `
                        <span class="px-3 py-1 bg-gray-200 text-gray-700 text-xs rounded-full">${tag}</span>
                    `).join('')}
                </div>
            ` : ''}

            <!-- Botões de resposta -->
            <div class="flex gap-3 justify-center flex-wrap">
                <button onclick="recordAnswer(${card.id}, 0)" class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition-colors">
                    ❌ Errei
                </button>
                <button onclick="recordAnswer(${card.id}, 1)" class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition-colors">
                    😐 Difícil
                </button>
                <button onclick="recordAnswer(${card.id}, 2)" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition-colors">
                    😊 OK
                </button>
                <button onclick="recordAnswer(${card.id}, 3)" class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors">
                    😎 Fácil
                </button>
            </div>
        </div>
    `;

    document.getElementById('study-container').innerHTML = html;
    isFlipped = false;

    // Adicionar event listener para virar card
    document.getElementById('card-flip').addEventListener('click', flipCard);
}

function flipCard() {
    isFlipped = !isFlipped;
    const cardInner = document.getElementById('card-inner');
    if (isFlipped) {
        cardInner.style.transform = 'rotateY(180deg)';
    } else {
        cardInner.style.transform = 'rotateY(0deg)';
    }
}

function recordAnswer(cardId, quality) {
    fetch('{{ route("anki.record-answer", $deck) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            card_id: cardId,
            quality: quality
        })
    }).then(() => {
        currentIndex++;
        renderCard();
    });
}

// Renderizar primeiro card ao carregar
document.addEventListener('DOMContentLoaded', renderCard);
</script>

<style>
#card-inner {
    transition: transform 0.6s;
}

#card-front, #card-back {
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
}

#card-back {
    transform: rotateY(180deg);
}
</style>
@endsection

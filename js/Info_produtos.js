const products = [
    {
        id: 1,
        name: 'T-shirt Preta KR Legends',
        modelName: 'KR Legends T-Shirt Onyx',
        price: 11.99,
        image: ['Imagens/Ilustracoes/Ilustração_frente-T-shirt_preta.png', 'Imagens/Ilustracoes/Ilustração costas - T-shirt preta.png'],
        stock: 5,
        color: 'Preto',
        gender: 'Unissex',
        shipping: 'Frete grátis para toda a zona do distrito de Aveiro',
        dateAdded: '2025-03-15',
        description: 'T‑shirt oficial da sua equipa de drift em Roblox, confeccionada em mistura premium de algodão e poliéster (50 / 50 ou 65 / 35) para máximo conforto, durabilidade e fidelidade na estampagem. Corte amplo XL que garante liberdade de movimento e visual arrojado; acabamento em preto sólido realça grafismos inspirados na estética “de rua” e design profissional de equipa de pilotos.',
        sizes: [
            { name: 'S', available: false },
            { name: 'M', available: false },
            { name: 'L', available: false },
            { name: 'XL', available: true },
            { name: 'XXL', available: false }
        ],
        Info: 'O frete fora do distrito de Aveiro terá que ser negociado mediante o local de entrega do produto. Cuidados: Lave do avesso em água fria (máx. 30 °C) com detergente neutro. Evite alvejante e secagem em tambor; prefira secagem à sombra. Passe a ferro em temperatura média pelo avesso para preservar cores e estampado.'
    },
    {
        id: 2,
        name: 'T-shirt Branca KR Legends',
        modelName: 'KR Legends T-Shirt Quartz',
        price: 10.99,
        image: ['Imagens/Ilustracoes/Ilustração_frente-T-shirt_branca.png', 'Imagens/Ilustracoes/Ilustração costas - T-shirt branca.png'],
        stock: 5,
        color: 'Branco',
        gender: 'Unissex',
        shipping: 'Frete grátis para toda a zona do distrito de Aveiro',
        dateAdded: '2025-03-15',
        description: 'T‑shirt oficial da sua equipa de drift em Roblox, confeccionada em mistura premium de algodão e poliéster (50 / 50 ou 65 / 35) para máximo conforto, durabilidade e fidelidade na estampagem. Corte amplo XL que garante liberdade de movimento e visual arrojado; acabamento em preto sólido realça grafismos inspirados na estética “de rua” e design profissional de equipa de pilotos.',
        sizes: [
            { name: 'S', available: false },
            { name: 'M', available: false },
            { name: 'L', available: false },
            { name: 'XL', available: true },
            { name: 'XXL', available: false }
        ],
        Info: 'O frete fora do distrito de Aveiro terá que ser negociado mediante o local de entrega do produto.\n\nCuidados: Lave do avesso em água fria (máx. 30 °C) com detergente neutro. Evite alvejante e secagem em tambor; prefira secagem à sombra. Passe a ferro em temperatura média pelo avesso para preservar cores e estampado.'
    },
    {
        id: 3,
        name: 'Camisola com Capuz Preta',
        modelName: 'KR Legends Sweat Midnight',
        price: 25.99,
        image: ['Imagens/Ilustracoes/Ilustração_frente-Camisola_preta.png', 'Imagens/Ilustracoes/Ilustração costas - Camisola preta.png'],
        stock: 5,
        color: 'Preto',
        gender: 'Unissex',
        shipping: 'Frete grátis para toda a zona do distrito de Aveiro',
        dateAdded: '2025-03-15',
        description: 'Uma t-shirt de alta qualidade feita com 100% algodão orgânico, oferecendo conforto durante todo o dia. O design minimalista com o logo da KR Legends na frente representa o verdadeiro espírito de corridas. Perfeita para qualquer ocasião casual.',
        sizes: [
            { name: 'S', available: false },
            { name: 'M', available: false },
            { name: 'L', available: false },
            { name: 'XL', available: true },
            { name: 'XXL', available: false }
        ],
        Info: 'O frete fora do distrito de Aveiro terá que ser negociado mediante o local de entrega do produto.\n\nCuidados: Lave do avesso em água fria (máx. 30 °C) com detergente neutro. Evite alvejante e secagem em tambor; prefira secagem à sombra. Passe a ferro em temperatura média pelo avesso para preservar cores e estampado.'
    },
    {
        id: 4,
        name: 'Camisola com Capuz Branca',
        modelName: 'KR Legends Sweat Sterling',
        price: 25.99,
        image: ['Imagens/Ilustracoes/Ilustração_frente-Camisola_branca.png', 'Imagens/Ilustracoes/Ilustração costas - Camisola branca.png'],
        stock: 5,
        color: 'Branco',
        gender: 'Unissex',
        shipping: 'Frete grátis para toda a zona do distrito de Aveiro',
        dateAdded: '2025-03-15',
        description: 'Uma t-shirt de alta qualidade feita com 100% algodão orgânico, oferecendo conforto durante todo o dia. O design minimalista com o logo da KR Legends na frente representa o verdadeiro espírito de corridas. Perfeita para qualquer ocasião casual.',
        sizes: [
            { name: 'S', available: false },
            { name: 'M', available: false },
            { name: 'L', available: false },
            { name: 'XL', available: true },
            { name: 'XXL', available: false }
        ],
        Info: 'O frete fora do distrito de Aveiro terá que ser negociado mediante o local de entrega do produto.\n\nCuidados: Lave do avesso em água fria (máx. 30 °C) com detergente neutro. Evite alvejante e secagem em tambor; prefira secagem à sombra. Passe a ferro em temperatura média pelo avesso para preservar cores e estampado.'
    },
    {
        id: 5, name: 'Novo lançamento em breve...',
        modelName: 'Modelo ainda sem nome',
        price: 0,
        image: ['Imagens/Ilustracoes/Em_breve.png'],
        stock: 0,
        color: '???',
        gender: '???',
        shipping: 'Frete grátis para toda a zona do distrito de Aveiro',
        dateAdded: '2024-03-15',
        description: 'Uma t-shirt de alta qualidade feita com 100% algodão orgânico, oferecendo conforto durante todo o dia. O design minimalista com o logo da KR Legends na frente representa o verdadeiro espírito de corridas. Perfeita para qualquer ocasião casual.',
        sizes: [
            { name: 'S', available: false },
            { name: 'M', available: false },
            { name: 'L', available: false },
            { name: 'XL', available: false },
            { name: 'XXL', available: false }
        ]
    },
    {
        id: 6, name: 'Novo lançamento em breve...',
        modelName: 'Modelo ainda sem nome',
        price: 0,
        image: ['Imagens/Ilustracoes/Em_breve.png'],
        stock: 0,
        color: '???',
        gender: '???',
        shipping: 'Frete grátis para toda a zona do distrito de Aveiro',
        dateAdded: '2024-03-15',
        description: 'Uma t-shirt de alta qualidade feita com 100% algodão orgânico, oferecendo conforto durante todo o dia. O design minimalista com o logo da KR Legends na frente representa o verdadeiro espírito de corridas. Perfeita para qualquer ocasião casual.',
        sizes: [
            { name: 'S', available: false },
            { name: 'M', available: false },
            { name: 'L', available: false },
            { name: 'XL', available: false },
            { name: 'XXL', available: false }
        ]
    },
    {
        id: 7, name: 'Novo lançamento em breve...',
        modelName: 'Modelo ainda sem nome',
        price: 0,
        image: ['Imagens/Ilustracoes/Em_breve.png'],
        stock: 0,
        color: '???',
        gender: '???',
        shipping: 'Frete grátis para toda a zona do distrito de Aveiro',
        dateAdded: '2024-03-15',
        description: 'Uma t-shirt de alta qualidade feita com 100% algodão orgânico, oferecendo conforto durante todo o dia. O design minimalista com o logo da KR Legends na frente representa o verdadeiro espírito de corridas. Perfeita para qualquer ocasião casual.',
        sizes: [
            { name: 'S', available: false },
            { name: 'M', available: false },
            { name: 'L', available: false },
            { name: 'XL', available: false },
            { name: 'XXL', available: false }
        ]
    },
    {
        id: 8, name: 'Novo lançamento em breve...',
        modelName: 'Modelo ainda sem nome',
        price: 0,
        image: ['Imagens/Ilustracoes/Em_breve.png'],
        stock: 0,
        color: '???',
        gender: '???',
        shipping: 'Frete grátis para toda a zona do distrito de Aveiro',
        dateAdded: '2024-03-15',
        description: 'Uma t-shirt de alta qualidade feita com 100% algodão orgânico, oferecendo conforto durante todo o dia. O design minimalista com o logo da KR Legends na frente representa o verdadeiro espírito de corridas. Perfeita para qualquer ocasião casual.',
        sizes: [
            { name: 'S', available: false },
            { name: 'M', available: false },
            { name: 'L', available: false },
            { name: 'XL', available: false },
            { name: 'XXL', available: false }
        ]
    },
    {
        id: 9, name: 'Novo lançamento em breve...',
        modelName: 'Modelo ainda sem nome',
        price: 0,
        image: ['Imagens/Ilustracoes/Em_breve.png'],
        stock: 0,
        color: '???',
        gender: '???',
        shipping: 'Frete grátis para toda a zona do distrito de Aveiro',
        dateAdded: '2024-03-15',
        description: 'Uma t-shirt de alta qualidade feita com 100% algodão orgânico, oferecendo conforto durante todo o dia. O design minimalista com o logo da KR Legends na frente representa o verdadeiro espírito de corridas. Perfeita para qualquer ocasião casual.',
        sizes: [
            { name: 'S', available: false },
            { name: 'M', available: false },
            { name: 'L', available: false },
            { name: 'XL', available: false },
            { name: 'XXL', available: false }
        ]
    },
    {
        id: 10, name: 'Novo lançamento em breve...',
        modelName: 'Modelo ainda sem nome',
        price: 0,
        image: ['Imagens/Ilustracoes/Em_breve.png'],
        stock: 0,
        color: '???',
        gender: '???',
        shipping: 'Frete grátis para toda a zona do distrito de Aveiro',
        dateAdded: '2024-03-15',
        description: 'Uma t-shirt de alta qualidade feita com 100% algodão orgânico, oferecendo conforto durante todo o dia. O design minimalista com o logo da KR Legends na frente representa o verdadeiro espírito de corridas. Perfeita para qualquer ocasião casual.',
        sizes: [
            { name: 'S', available: false },
            { name: 'M', available: false },
            { name: 'L', available: false },
            { name: 'XL', available: false },
            { name: 'XXL', available: false }
        ]
    },
];
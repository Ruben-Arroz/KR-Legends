const faqs = [
  // Exemplo de FAQ com tópicos
  {
    id: 'faq1',
    question: 'Como redefinir a palavra-passe da tua conta do site KR Legends?',
    answer: {
      intro: 'Siga estes passos:',
      points: [
        'Acede ao site e inicia sessão com as tuas credenciais atuais.',
        'Clica no ícone de engrenagem no canto superior direito e seleciona Definições.',
        'Vai a Conta > Palavra-passe e Segurança e clica em Alterar palavra-passe.',
        'Insere a tua palavra-passe atual no primeiro campo.',
        'No campo “Nova palavra-passe”, escolhe uma password de 8–20 caracteres, incluindo uma maiúscula, uma minúscula, um número e um símbolo.',
        'Repete a nova palavra-passe no campo de confirmação.',
        'Clica em Guardar alterações.',
        'Abre o e-mail de confirmação enviado e segue o link para validar a alteração. Se não receberes, verifica o SPAM ou solicita o reenvio na mesma secção de Definições.'
      ]
    }
  },
  {
    id: 'faq2',
    question: 'O que faço se não consigo iniciar sessão no site?',
    answer: {
      intro: 'Experimente o seguinte:',
      points: [
        'Verifique se está a utilizar o e-mail e a palavra-passe corretos, prestando atenção ao Caps Lock.',
        'Limpe a cache e os cookies do navegador ou tente iniciar sessão noutro navegador/dispositivo.',
        'Consulte as nossas redes sociais (Instagram, Discord, etc.) para quaisquer avisos de manutenção ou atualizações.',
        'Aguarde alguns minutos e volte a tentar; por vezes aplicamos atualizações que impedem o acesso temporariamente.',
        'Se ainda não conseguir, envie-nos uma mensagem detalhando o problema (captura de ecrã, mensagem de erro) através do nosso Centro de Suporte.'
      ]
    }
  },  
  {
    id: 'faq3',
    question: 'Quais são os requisitos mínimos do PC para jogar o KR Legends?',
    answer: {
      intro: 'Para assegurar que o KR Legends funcione corretamente, verifique os seguintes requisitos mínimos:',
      points: [
        'Windows 7, 8/8.1 ou 10 em 64 bits',
        'Processador dual-core ≥ 1,6 GHz (ex.: Intel Core 2 Duo, AMD Athlon 64)',
        '1 GB de RAM (recomendado 4 GB para maior estabilidade)',
        'Placa gráfica compatível com DirectX 10+ (Intel HD 4000+, Nvidia GeForce 8 Series+, AMD Radeon HD 2000+)',
        'Shader Model 4.0 ou superior',
        '20 MB de espaço livre em disco (recomendado SSD e ≥ 1 GB para caches)',
        'Ligação à Internet estável (4–8 Mb/s) para carregamento de assets e latência suave'
      ]
    }
  },
  {
    id: 'faq4',
    question: 'Esqueci-me da palavra-passe do site. Como posso recuperar?',
    answer: {
      intro: 'Segue estes passos para recuperares o acesso à tua conta do site KR Legends:',
      points: [
        'Vai à página de início de sessão no site.',
        'Clica em “Esqueci a senha”.',
        'Escreve o e-mail associado à tua conta do site.',
        'Verifica a tua caixa de entrada e clica no link enviado.',
        'Cria uma nova palavra-passe que cumpra os requisitos de segurança.',
        'Confirma a alteração e tenta iniciar sessão com a nova palavra-passe.',
        'Se não receberes o e-mail, verifica o SPAM ou tenta novamente passados alguns minutos.',
        'Em caso de falha contínua, contacta o Suporte através do formulário no site ou pelas nossas redes sociais.'
      ]
    }
  },
  {
    id: 'faq5',
    question: 'Como posso apagar a minha conta do site KR Legends?',
    answer: {
      intro: 'Podes eliminar a tua conta do site KR Legends diretamente nas tuas definições. Segue estes passos:',
      points: [
        'Inicia sessão com a conta que pretendes apagar.',
        'Clica no ícone de engrenagem no canto superior direito e entra em “Definições”.',
        'Vai a Conta > Gestão da Conta.',
        'Clica em “Eliminar Conta” no final da página.',
        'Confirma a ação inserindo a tua palavra-passe.',
        'Serás alertado sobre a perda de dados associada à tua conta.',
        'Confirma novamente para concluir a eliminação definitiva.',
        'Caso não consigas concluir o processo, entra em contacto com o Suporte.'
      ]
    }
  },
  {
    id: 'faq6',
    question: 'Porque não consigo iniciar sessão no site mesmo com os dados certos?',
    answer: {
      intro: 'Se inseriste os dados corretos mas continuas sem conseguir entrar, segue estas orientações:',
      points: [
        'Confirma se estás a usar os dados de conta do site KR Legends, e não da conta Roblox.',
        'Verifica se há erros de digitação, como espaços antes/depois do e-mail ou palavra-passe.',
        'Limpa a cache do navegador ou tenta noutro navegador.',
        'Garante que tens ligação à internet estável.',
        'É possível que o site esteja em manutenção temporária.',
        'Se o problema persistir, entra em contacto com o Suporte através do formulário ou das redes sociais oficiais.'
      ]
    }
  },
  {
    id: 'faq7',
    question: 'É possível usar a conta Roblox para entrar no site?',
    answer: `Não. O site do KR Legends utiliza um sistema de contas próprio, separado do Roblox. Por isso, precisas de criar uma conta específica no nosso site para poderes iniciar sessão, comentar, dar sugestões e aceder a funcionalidades exclusivas da comunidade.`
  },
  {
    id: 'faq8',
    question: 'O que faço se encontrar outro jogador a violar as regras?',
    answer: {
      intro: 'Segue estes passos para reportar um jogador:',
      points: [
        'Acede ao formulário de Suporte no site KR Legends.',
        'Seleciona a opção relacionada com denúncias ou comportamento abusivo.',
        'Escreve o nome do jogador envolvido e descreve com clareza o que aconteceu.',
        'Anexa capturas de ecrã ou vídeo, se tiveres provas.',
        'Em alternativa, também podes contactar-nos através das redes sociais oficiais.',
        'As tuas denúncias são confidenciais e serão tratadas com seriedade.'
      ]
    }
  },

];
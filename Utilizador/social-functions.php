<?php
/**
 * Funções para gerenciamento de redes sociais
 */

/**
 * Verifica se uma URL é válida
 *
 * @param string $url URL a ser verificada
 * @return bool Retorna true se a URL for válida, false caso contrário
 */
function isValidUrl($url)
{
    if (empty($url)) {
        return false;
    }

    // Adiciona http:// se não tiver protocolo
    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
        $url = 'http://' . $url;
    }

    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Verifica se pelo menos uma rede social tem URL válida
 *
 * @param array $socials Array com as redes sociais
 * @return bool Retorna true se pelo menos uma rede social tiver URL válida
 */
function hasAnySocialMedia($socials)
{
    if (!is_array($socials)) {
        return false;
    }

    foreach ($socials as $social => $url) {
        if ($social !== 'SOCIAL_ID' && $social !== 'EMAIL_SOCIAL' && !empty($url)) {
            if (isValidUrl($url)) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Obtém o ícone Font Awesome para uma rede social específica
 *
 * @param string $socialName Nome da rede social
 * @return string Classe do ícone Font Awesome
 */
function getSocialIcon($socialName)
{
    $icons = [
        'EMAIL' => 'fab fa-google',
        'WHATSAPP' => 'fab fa-whatsapp',
        'YOUTUBE' => 'fab fa-youtube',
        'INSTAGRAM' => 'fab fa-instagram',
        'TIKTOK' => 'fab fa-tiktok',
        'FACEBOOK' => 'fab fa-facebook',
        'LINKEDIN' => 'fab fa-linkedin',
        'GITHUB' => 'fab fa-github',
        'TWITTER' => 'fab fa-twitter'
    ];

    return $icons[$socialName] ?? 'fas fa-link';
}

/**
 * Obtém o nome de exibição para uma rede social
 *
 * @param string $socialName Nome da rede social no banco de dados
 * @return string Nome de exibição da rede social
 */
function getSocialDisplayName($socialName)
{
    $displayNames = [
        'EMAIL' => 'E-mail',    
        'WHATSAPP' => 'WhatsApp',
        'YOUTUBE' => 'YouTube',
        'INSTAGRAM' => 'Instagram',
        'TIKTOK' => 'TikTok',
        'FACEBOOK' => 'Facebook',
        'LINKEDIN' => 'LinkedIn',
        'GITHUB' => 'GitHub',
        'TWITTER' => 'Twitter'
    ];

    return $displayNames[$socialName] ?? $socialName;
}
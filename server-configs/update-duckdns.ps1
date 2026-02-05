# Script PowerShell para atualizar DuckDNS IP
# Execute este script no Windows para atualizar o IP do DuckDNS

# Configurações
$domain = "tstjoinenglish"
$token = "SEU_TOKEN_AQUI"  # Substitua pelo seu token do DuckDNS

# Obter IP público
try {
    $publicIP = (Invoke-WebRequest -Uri "https://api.ipify.org" -UseBasicParsing).Content
    Write-Host "Seu IP público: $publicIP" -ForegroundColor Green
    
    # Atualizar DuckDNS
    $url = "https://www.duckdns.org/update?domains=$domain&token=$token&ip=$publicIP"
    $response = (Invoke-WebRequest -Uri $url -UseBasicParsing).Content
    
    if ($response -eq "OK") {
        Write-Host "DuckDNS atualizado com sucesso!" -ForegroundColor Green
        Write-Host "Domínio: $domain.duckdns.org -> $publicIP" -ForegroundColor Cyan
    } else {
        Write-Host "Erro ao atualizar DuckDNS: $response" -ForegroundColor Red
    }
} catch {
    Write-Host "Erro: $_" -ForegroundColor Red
}

# Verificar DNS
Write-Host "`nVerificando DNS..." -ForegroundColor Yellow
try {
    $dnsIP = (Resolve-DnsName -Name "$domain.duckdns.org" -Type A).IPAddress
    Write-Host "$domain.duckdns.org resolve para: $dnsIP" -ForegroundColor Cyan
    
    if ($dnsIP -eq $publicIP) {
        Write-Host "DNS está correto!" -ForegroundColor Green
    } else {
        Write-Host "Atenção: DNS não corresponde ao seu IP público" -ForegroundColor Yellow
        Write-Host "Pode levar alguns minutos para propagar" -ForegroundColor Yellow
    }
} catch {
    Write-Host "Erro ao verificar DNS: $_" -ForegroundColor Red
}

Read-Host "`nPressione Enter para sair"

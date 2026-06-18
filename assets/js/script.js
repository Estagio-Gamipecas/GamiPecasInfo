// Carregar dashboard
async function carregarDashboard() {
    try {
        const response = await fetch('../api/dashboard.php');
        const data = await response.json();
        
        // Atualizar cards
        document.getElementById('totalProdutos').textContent = data.total_produtos;
        document.getElementById('stockBaixo').textContent = data.stock_baixo;
        document.getElementById('regrasAtivas').textContent = data.regras_ativas;
        document.getElementById('notificacoesHoje').textContent = data.notificacoes_hoje;
        
        // Produtos críticos
        const produtosTbody = document.getElementById('produtosCriticos');
        if (data.produtos_criticos.length === 0) {
            produtosTbody.innerHTML = '<tr><td colspan="5" style="text-align:center">✅ Nenhum produto com stock crítico</td></tr>';
        } else {
            produtosTbody.innerHTML = data.produtos_criticos.map(p => `
                <tr>
                    <td><strong>${p.nome}</strong></td>
                    <td class="status-badge status-falhou">${p.quantidade}</td>
                    <td>${p.quantidade_minima}</td>
                    <td class="status-badge status-falhou">${p.falta}</td>
                    <td><button class="btn-primary" style="padding:6px 12px;font-size:12px" onclick="window.location.href='pages/produtos.html'">Repor</button></td>
                </tr>
            `).join('');
        }
        
        // Últimas notificações
        const notificacoesTbody = document.getElementById('ultimasNotificacoes');
        if (data.ultimas_notificacoes.length === 0) {
            notificacoesTbody.innerHTML = '<tr><td colspan="5" style="text-align:center">📭 Nenhuma notificação enviada ainda</td></tr>';
        } else {
            notificacoesTbody.innerHTML = data.ultimas_notificacoes.map(n => `
                <tr>
                    <td>${new Date(n.data_envio).toLocaleString('pt-PT')}</td>
                    <td>${n.regra_nome || '-'}</td>
                    <td>${n.produto_nome || '-'}</td>
                    <td>${n.utilizador_email}</td>
                    <td><span class="status-badge status-${n.status}">${n.status === 'enviado' ? '✅ Enviado' : '❌ Falhou'}</span></td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Erro:', error);
    }
}

// Atualizar a cada 30 segundos
carregarDashboard();
setInterval(carregarDashboard, 30000);
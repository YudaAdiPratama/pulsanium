<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pulsanium Barter — VEX & ETH</title>
    <script src="{{ asset('js/vex.min.js') }}"></script>
    <script src="{{ asset('js/scatterjs-core.min.js') }}"></script>
    <script src="{{ asset('js/scatterjs-plugin-vexjs.min.js') }}"></script>
    <script src="{{ asset('js/pocket.vex.min.js') }}"></script>
    <style>
        :root { color-scheme: light; --bg:#f4f8f7; --panel:#ffffff; --line:#d9e4e1; --text:#14201d; --muted:#64736f; --green:#0eaaa0; --lime:#8edb00; --blue:#4169d8; --danger:#c93655; font-family:Inter,ui-sans-serif,system-ui,sans-serif }
        * { box-sizing:border-box } body { margin:0; color:var(--text); background:radial-gradient(circle at 12% 0,rgba(16,191,179,.16) 0,transparent 32%),radial-gradient(circle at 88% 8%,rgba(142,219,0,.12) 0,transparent 28%),linear-gradient(180deg,#fcfefd 0,var(--bg) 100%) }
        main { width:min(1040px,calc(100% - 28px)); margin:32px auto 64px } h1,h2,p { margin-top:0 } h1 { font-size:clamp(2rem,6vw,4rem); line-height:1; letter-spacing:-.05em; max-width:720px }
        .brand { display:flex;align-items:center;gap:15px;margin-bottom:24px }.brand img { width:76px;height:76px;object-fit:cover;border-radius:20px;box-shadow:0 10px 30px rgba(16,191,179,.18) }.brand-name { display:block;color:var(--text);font-size:1.35rem;font-weight:900;letter-spacing:-.03em }.brand-caption { display:block;color:var(--muted);font-size:.84rem;margin-top:3px }.lead { max-width:680px;color:var(--muted);font-size:1.05rem }
        .card { background:rgba(255,255,255,.96);border:1px solid var(--line);border-radius:20px;padding:24px;margin-top:18px;box-shadow:0 18px 55px rgba(31,71,60,.10) }
        .methods,.grid,.summary { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px }.method { position:relative;border:1px solid var(--line);border-radius:15px;padding:18px;cursor:pointer;background:#f8fbfa }.method:has(input:checked) { border-color:var(--green);background:#effbf7;box-shadow:inset 0 0 0 1px var(--green) }.method input { position:absolute;opacity:0 }.method strong { font-size:1.1rem }.method small { display:block;color:var(--muted);margin-top:5px }
        label { display:block;font-weight:750;margin:17px 0 7px } input,select,textarea,button { width:100%;border:1px solid #cbdad6;border-radius:11px;padding:12px 13px;font:inherit } input,select,textarea { background:#ffffff;color:var(--text) } input:focus,select:focus,textarea:focus { outline:3px solid rgba(22,168,121,.15);border-color:var(--green) } button { border:0;background:var(--green);color:#ffffff;font-weight:850;cursor:pointer } button.secondary { background:#e4ece9;color:var(--text) } button:disabled { opacity:.5;cursor:not-allowed }
        .wallet-head { display:flex;justify-content:space-between;gap:14px;align-items:center }.wallet-actions { display:flex;gap:9px;width:auto }.wallet-actions button { width:auto;white-space:nowrap }.wallet-data,.summary { margin-top:15px }.datum { background:#f7faf9;border:1px solid var(--line);border-radius:12px;padding:13px;min-width:0 }.datum span { display:block;color:var(--muted);font-size:.8rem;margin-bottom:4px }.datum strong { overflow-wrap:anywhere }.accent { color:#087b58 }
        .notice { padding:14px 16px;border-radius:12px;margin-top:16px;background:#eaf3fb;color:#254d6b }.notice.error { background:#ffedf1;color:#94243d }.notice.success { background:#e7f8f1;color:#146247 }.notice ul { margin:7px 0 0;padding-left:18px }.hint { color:var(--muted);font-size:.88rem;margin-top:8px }.hidden { display:none!important }.pay { margin-top:18px;padding:15px;font-size:1rem }.method-tag { color:var(--blue) }
        @media(max-width:680px){.methods,.grid,.summary{grid-template-columns:1fr}.wallet-head{align-items:flex-start;flex-direction:column}.wallet-actions{width:100%}.wallet-actions button{width:100%}.card{padding:18px}.brand img{width:64px;height:64px}.brand{margin-bottom:20px}}
    </style>
</head>
<body>
<main>
    <div class="brand">
        <img src="{{ asset('images/pulsanium-logo-v2.png') }}" alt="Logo Pulsanium" width="76" height="76">
        <div><span class="brand-name">Pulsanium</span><span class="brand-caption">Barter pulsa cepat dan aman</span></div>
    </div>
    <h1>Barter pulsa dengan VEX atau ETH.</h1>
    @if ($errors->any())
        <div class="notice error"><strong>Pesanan belum dapat diproses.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @if (session('success_order'))
        @php($success = session('success_order'))
        <div class="notice success"><strong>Pesanan {{ $success['id'] }} diterima.</strong><br>{{ $success['product_description'] }} untuk {{ $success['phone'] }} · status <b>{{ $success['status'] }}</b>.@if(!empty($success['last_error']))<br><small>GOWA akan perlu dicoba kembali: {{ $success['last_error'] }}</small>@endif</div>
    @endif

    <form id="purchase-form" method="post" action="{{ route('purchase.store') }}">
        @csrf
        <input type="hidden" name="wallet_account" id="wallet-account-input" value="{{ old('wallet_account') }}">
        <input type="hidden" name="chain_id" id="chain-id-input" value="{{ old('chain_id') }}">
        <input type="hidden" name="transaction_id" id="transaction-id-input" value="{{ old('transaction_id') }}">
        <input type="hidden" name="transaction_result" id="transaction-result-input" value="{{ old('transaction_result') }}">

        <section class="card">
            <h2>1. Pilih metode barter</h2>
            <div class="methods">
                <label class="method"><input type="radio" name="payment_method" value="vex" @checked(old('payment_method','vex') === 'vex')><strong>Vexanium <span class="method-tag">VEX</span></strong><small>Transfer melalui VexWallet</small></label>
                <label class="method"><input type="radio" name="payment_method" value="eth" @checked(old('payment_method') === 'eth')><strong>Ethereum <span class="method-tag">ETH</span></strong><small>Transfer melalui MetaMask · Mainnet</small></label>
            </div>
        </section>

        <section class="card">
            <div class="wallet-head">
                <div><h2>2. Hubungkan wallet</h2><p id="wallet-message" class="hint">Pilih metode lalu hubungkan wallet.</p></div>
                <div class="wallet-actions"><button type="button" id="connect-button">Hubungkan VexWallet</button><button type="button" class="secondary hidden" id="disconnect-button">Putuskan</button></div>
            </div>
            <div id="wallet-data" class="grid wallet-data hidden">
                <div class="datum"><span>Akun wallet</span><strong id="wallet-account" class="accent">-</strong></div>
                <div class="datum"><span>Saldo</span><strong id="wallet-balance">-</strong></div>
            </div>
        </section>

        <section class="card">
            <h2>3. Detail pembelian</h2>
            <div class="grid">
                <div><label for="phone">Nomor HP</label><input id="phone" name="phone" inputmode="numeric" autocomplete="tel" minlength="10" maxlength="15" required value="{{ old('phone') }}" placeholder="08xxxxxxxxxx"><p id="operator-hint" class="hint">Operator terdeteksi otomatis dari prefix.</p></div>
                <div><label for="operator">Operator</label><input id="operator" readonly value="Belum terdeteksi"></div>
            </div>
            <label for="package">Produk pulsa</label>
            <select id="package" name="package" required disabled>
                <option value="">Masukkan nomor HP lebih dahulu</option>
                @foreach($products as $product)
                    <option value="{{ $product['key'] }}" data-operator="{{ $product['operator'] }}" @selected(old('package') === $product['key'])>{{ $product['description'] }} — Rp {{ number_format($product['price'],0,',','.') }}</option>
                @endforeach
            </select>
            <label for="note">Catatan (opsional)</label><textarea id="note" name="note" rows="2" maxlength="200" placeholder="Catatan untuk pesanan">{{ old('note') }}</textarea>
            <div class="summary">
                <div class="datum"><span>Harga pulsa</span><strong id="total-idr">-</strong></div>
                <div class="datum"><span>Total barter</span><strong id="total-crypto" class="accent">-</strong></div>
                <div class="datum"><span>Kurs</span><strong id="rate-info">-</strong></div>
                <div class="datum"><span>Penerima</span><strong id="merchant-info">-</strong></div>
            </div>
            <p id="payment-message" class="hint">Hubungkan wallet untuk melanjutkan.</p>
            <button id="pay-button" class="pay" type="submit" disabled>Barter dengan VEX</button>
        </section>
    </form>
</main>

<script>
const QUOTES = @json($quotes);
const CONFIG = {
    vexMerchant: @json(config('pulsanium.vex.merchant')),
    ethMerchant: @json(config('pulsanium.eth.merchant')),
    ethChainId: @json(strtolower(config('pulsanium.eth.chain_id')))
};
const operators = {
    Telkomsel:['0852','0853','0811','0812','0813','0821','0822','0823'], BYU:['0851'],
    Indosat:['0855','0856','0857','0858','0814','0815','0816'], XL:['0817','0818','0819','0859','0877','0878'],
    Axis:['0832','0833','0838'], Three:['0895','0896','0897','0898','0899'],
    Smartfren:['0881','0882','0883','0884','0885','0886','0887','0888','0889']
};
const form=document.getElementById('purchase-form'), connectBtn=document.getElementById('connect-button'), disconnectBtn=document.getElementById('disconnect-button');
const walletInput=document.getElementById('wallet-account-input'), chainInput=document.getElementById('chain-id-input'), txInput=document.getElementById('transaction-id-input'), resultInput=document.getElementById('transaction-result-input');
const walletData=document.getElementById('wallet-data'), walletAccount=document.getElementById('wallet-account'), walletBalance=document.getElementById('wallet-balance'), walletMessage=document.getElementById('wallet-message');
const phone=document.getElementById('phone'), operatorField=document.getElementById('operator'), operatorHint=document.getElementById('operator-hint'), packageSelect=document.getElementById('package');
const totalIdr=document.getElementById('total-idr'), totalCrypto=document.getElementById('total-crypto'), rateInfo=document.getElementById('rate-info'), merchantInfo=document.getElementById('merchant-info'), paymentMessage=document.getElementById('payment-message'), payBtn=document.getElementById('pay-button');
let vexIdentity=null;
let vexMobileBridge=false;
const selectedMethod=()=>document.querySelector('input[name="payment_method"]:checked').value;
const selectedQuote=()=>packageSelect.value && QUOTES[packageSelect.value] ? QUOTES[packageSelect.value][selectedMethod()] : null;
const currency=()=>selectedMethod()==='vex'?'VEX':'ETH';

function detectOperator(value){const prefix=value.replace(/\D/g,'').slice(0,4);for(const [name,prefixes] of Object.entries(operators)){if(prefixes.includes(prefix))return{name,database:(name==='BYU'?'TELKOMSEL':name).toUpperCase()}}return null}
function updateProducts(){const found=detectOperator(phone.value);operatorField.value=found?found.name:(phone.value.replace(/\D/g,'').length>=4?'Tidak dikenali':'Belum terdeteksi');operatorHint.textContent=found?'Produk '+found.name+' tersedia di bawah.':'Operator terdeteksi otomatis dari prefix.';packageSelect.disabled=!found;for(const option of packageSelect.options){if(!option.value){option.textContent=found?'Pilih produk pulsa':'Masukkan nomor HP lebih dahulu';continue}option.hidden=!found||option.dataset.operator!==found.database}if(packageSelect.selectedOptions[0]?.hidden)packageSelect.value='';updateSummary()}
function updateSummary(){const quote=selectedQuote(), method=selectedMethod();const product=packageSelect.value?QUOTES[packageSelect.value]:null;totalIdr.textContent=product?new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(product.price):'-';totalCrypto.textContent=quote?`${quote.amount} ${currency()}`:'-';rateInfo.textContent=quote?`1 ${currency()} = ${new Intl.NumberFormat('id-ID').format(quote.rate)} IDR (${quote.source})`:'-';merchantInfo.textContent=method==='vex'?CONFIG.vexMerchant:CONFIG.ethMerchant;payBtn.textContent=`Barter dengan ${currency()}`;payBtn.disabled=!walletInput.value||!quote||(method==='eth'&&chainInput.value.toLowerCase()!==CONFIG.ethChainId)}
function resetWallet(){walletInput.value='';chainInput.value='';txInput.value='';resultInput.value='';vexIdentity=null;vexMobileBridge=false;walletData.classList.add('hidden');disconnectBtn.classList.add('hidden');connectBtn.classList.remove('hidden');walletBalance.textContent='-';walletMessage.textContent='Wallet belum terhubung.';updateSummary()}
function showWallet(account,balance,chainId=''){walletInput.value=account;chainInput.value=chainId;walletAccount.textContent=account;walletBalance.textContent=balance;walletData.classList.remove('hidden');connectBtn.classList.add('hidden');disconnectBtn.classList.remove('hidden');walletMessage.textContent=selectedMethod()==='vex'?'VexWallet terhubung.':'MetaMask terhubung ke Ethereum Mainnet.';updateSummary()}
function changeMethod(){resetWallet();connectBtn.textContent=selectedMethod()==='vex'?'Hubungkan VexWallet':'Hubungkan MetaMask';paymentMessage.textContent='Hubungkan wallet untuk melanjutkan.';updateSummary()}

const hasVexMobileBridge=()=>typeof window.DappJsBridge?.pushMessage==='function'||typeof window.webkit?.messageHandlers?.pushMessage?.postMessage==='function';
async function waitForVexMobileBridge(timeout=3000){const started=Date.now();while(Date.now()-started<timeout){if(hasVexMobileBridge())return true;await new Promise(resolve=>setTimeout(resolve,100))}return false}
function callVexMobileBridge(method,params=''){return new Promise((resolve,reject)=>{const serialNumber=`pulsanium${Date.now()}${Math.floor(Math.random()*100000)}`;const previous=window.callbackResult;const timer=setTimeout(()=>{window.callbackResult=previous;reject(new Error('VexWallet tidak merespons. Coba buka ulang DApp Browser.'))},10000);window.callbackResult=(serial,result)=>{if(serial!==serialNumber){if(typeof previous==='function')previous(serial,result);return}clearTimeout(timer);window.callbackResult=previous;try{resolve(typeof result==='string'?JSON.parse(result):result)}catch{reject(new Error('Respons VexWallet tidak valid.'))}};try{if(typeof window.DappJsBridge?.pushMessage==='function')window.DappJsBridge.pushMessage(serialNumber,typeof params==='string'?params:JSON.stringify(params),method);else window.webkit.messageHandlers.pushMessage.postMessage({params:typeof params==='string'?params:JSON.stringify(params),serialNumber,methodName:method})}catch(error){clearTimeout(timer);window.callbackResult=previous;reject(error)}})}
if(typeof ScatterJS!=='undefined'&&typeof Vexanium==='function')ScatterJS.plugins(Vexanium());
const vexNetwork=typeof ScatterJS!=='undefined'?ScatterJS.Network.fromJson({blockchain:bc('vex'),chainId:'f9f432b1851b5c179d2091a96f593aaed50ec7466b74f89301f957a83e56ce1f',host:'vexascan.com',port:8443,protocol:'https'}):null;
async function connectVex(){walletMessage.textContent='Menghubungkan ke VexWallet...';if(await waitForVexMobileBridge()){const res=await callVexMobileBridge('getWalletWithAccount');const account=res?.data?.account||res?.account;if(!account)throw new Error(res?.message||'Akun VEX tidak terbaca. Pastikan wallet sudah dibuka.');vexMobileBridge=true;vexIdentity={name:account,authority:'active'};showWallet(account,'Memuat saldo...');try{const balRes=await callVexMobileBridge('getEosBalance',{account,contract:'vex.token',symbol:'VEX'});walletBalance.textContent=balRes?.data?.balance||balRes?.balance||balRes?.data?.quantity||balRes?.quantity||'Saldo tidak terbaca'}catch(e){walletBalance.textContent='Saldo tidak terbaca'}return}if(typeof ScatterJS==='undefined'||!vexNetwork)throw new Error('VexWallet tidak ditemukan. Buka situs ini dari DApp Browser VexWallet.');const connected=await ScatterJS.connect('Pulsanium Barter Pulsa',{network:vexNetwork});if(!connected)throw new Error('VexWallet belum terbuka atau izin ditolak.');const identity=await ScatterJS.login();if(!identity?.accounts?.[0])throw new Error('Login VexWallet dibatalkan.');vexIdentity=identity.accounts[0];showWallet(vexIdentity.name,'Memuat saldo...');try{const info=await VexNet(vexNetwork).getAccount(vexIdentity.name);walletBalance.textContent=info.core_liquid_balance||'0.0000 VEX'}catch{walletBalance.textContent='Saldo tidak terbaca'}}
async function connectEth(){if(!window.ethereum)throw new Error('MetaMask tidak ditemukan.');const accounts=await window.ethereum.request({method:'eth_requestAccounts'});let chainId=(await window.ethereum.request({method:'eth_chainId'})).toLowerCase();if(chainId!==CONFIG.ethChainId){await window.ethereum.request({method:'wallet_switchEthereumChain',params:[{chainId:CONFIG.ethChainId}]});chainId=(await window.ethereum.request({method:'eth_chainId'})).toLowerCase()}const balance=await window.ethereum.request({method:'eth_getBalance',params:[accounts[0],'latest']});showWallet(accounts[0],`${(Number(BigInt(balance))/1e18).toFixed(6)} ETH`,chainId)}
async function connect(){connectBtn.disabled=true;try{selectedMethod()==='vex'?await connectVex():await connectEth()}catch(error){walletMessage.textContent=error?.message||'Wallet gagal terhubung.'}finally{connectBtn.disabled=false}}
function vexQuantity(value){const number=Number(value);if(!Number.isFinite(number)||number<=0)throw new Error('Jumlah VEX tidak valid.');return number.toFixed(4)}
async function payVex(quote){if(!vexIdentity)throw new Error('Hubungkan VexWallet terlebih dahulu.');const quantity=vexQuantity(quote.amount),memo=`PULSA-${vexIdentity.name.toUpperCase()}`.slice(0,255);let result;if(vexMobileBridge){if(typeof window.pe?.pushTransfer!=='function')throw new Error('Fitur transfer VexWallet tidak tersedia. Perbarui aplikasi VexWallet.');result=await window.pe.pushTransfer({serialNumber:`pulsanium${Date.now()}${Math.floor(Math.random()*100000)}`,protocol:'TokenPocket',version:'v1.0',blockchain:'vex',action:'transfer',from:vexIdentity.name,to:CONFIG.vexMerchant,amount:quantity,quantity:`${quantity} VEX`,contract:'vex.token',symbol:'VEX',precision:4,memo});if(result?.result===0||result?.success===false||result?.code===4001)throw new Error(result?.message||'Transfer dibatalkan di VexWallet.')}else{const contract=await VexNet(vexNetwork).contract('vex.token');result=await contract.transfer({from:vexIdentity.name,to:CONFIG.vexMerchant,quantity:`${quantity} VEX`,memo},{authorization:`${vexIdentity.name}@${vexIdentity.authority||'active'}`})}const id=result?.txID||result?.txId||result?.transaction_id||result?.transactionId||result?.id||result?.data?.txID||result?.data?.transaction_id||result?.data?.transactionId||result?.processed?.id;if(!id)throw new Error(result?.message||'Transfer diproses tetapi TX ID tidak terbaca.');txInput.value=id;resultInput.value=JSON.stringify(result)}
function ethToWeiHex(amount){const [whole,fraction='']=amount.split('.');const wei=BigInt(whole)*(10n**18n)+BigInt(fraction.padEnd(18,'0').slice(0,18));return`0x${wei.toString(16)}`}
async function waitReceipt(hash){for(let i=0;i<48;i++){const receipt=await window.ethereum.request({method:'eth_getTransactionReceipt',params:[hash]});if(receipt){if(receipt.status!=='0x1')throw new Error('Transaksi Ethereum gagal.');return receipt}await new Promise(resolve=>setTimeout(resolve,2500))}throw new Error('Konfirmasi melewati 2 menit. Periksa MetaMask sebelum mencoba lagi.')}
async function payEth(quote){const hash=await window.ethereum.request({method:'eth_sendTransaction',params:[{from:walletInput.value,to:CONFIG.ethMerchant,value:ethToWeiHex(quote.amount)}]});txInput.value=hash;paymentMessage.textContent=`Transaksi ${hash} dikirim. Menunggu konfirmasi...`;await waitReceipt(hash)}
async function submitPayment(event){event.preventDefault();if(!form.reportValidity())return;const quote=selectedQuote();if(!quote||!walletInput.value){paymentMessage.textContent='Lengkapi wallet, nomor HP, dan produk.';return}payBtn.disabled=true;paymentMessage.textContent=`Konfirmasi transfer ${quote.amount} ${currency()} di wallet.`;try{selectedMethod()==='vex'?await payVex(quote):await payEth(quote);paymentMessage.textContent='Barter terkonfirmasi. Menyimpan pesanan dan meneruskan pulsa...';form.submit()}catch(error){paymentMessage.textContent=error?.code===4001?'Barter dibatalkan.':(error?.message||'Barter gagal.');updateSummary()}}

document.querySelectorAll('input[name="payment_method"]').forEach(input=>input.addEventListener('change',changeMethod));connectBtn.addEventListener('click',connect);disconnectBtn.addEventListener('click',resetWallet);phone.addEventListener('input',updateProducts);packageSelect.addEventListener('change',updateSummary);form.addEventListener('submit',submitPayment);
if(window.ethereum){window.ethereum.on('accountsChanged',accounts=>{if(selectedMethod()==='eth'&&!accounts.length)resetWallet()});window.ethereum.on('chainChanged',chain=>{if(selectedMethod()==='eth'){chainInput.value=chain.toLowerCase();updateSummary()}})}
changeMethod();updateProducts();
</script>

<a href="https://www.pulsawae.com/reports/pembelian" class="pulsanium-report-link">Laporan Pembelian</a>
<style>
.pulsanium-report-link {
    position: absolute;
    top: 48px;
    right: max(20px, calc((100vw - 1040px) / 2));
    z-index: 1000;
    display: inline-flex;
    align-items: center;
    padding: 10px 16px;
    border: 1px solid rgba(37, 99, 235, .18);
    border-radius: 10px;
    background: #ffffff;
    color: #2563eb !important;
    font: 600 14px/1.2 Arial, sans-serif;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(15, 23, 42, .10);
}
.pulsanium-report-link:hover {
    background: #eff6ff;
}
@media (max-width: 640px) {
    .pulsanium-report-link {
        top: 20px;
        right: 16px;
        padding: 8px 12px;
        font-size: 12px;
    }
}
</style>
</body>
</html>

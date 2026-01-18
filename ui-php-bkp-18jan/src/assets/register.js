function ok(el){ el.innerHTML="✔"; el.style.color="green"; }
function err(el,msg){ el.innerHTML=msg; el.style.color="red"; }

username.onkeyup = () => {
 fetch(`api/check_username.php?u=${username.value}`)
 .then(r=>r.json())
 .then(d=> d.ok ? ok(uStatus) : err(uStatus,"Exists"));
};

email.onkeyup = () => {
 /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)
 ? ok(eStatus)
 : err(eStatus,"Invalid");
};

mobile.onkeyup = () => {
 /^[6-9]\d{9}$/.test(mobile.value)
 ? ok(mStatus)
 : err(mStatus,"Invalid");
};

password.onkeyup = () => {
 password.value.length >= 8
 ? ok(pStatus)
 : err(pStatus,"Min 8 chars");
};


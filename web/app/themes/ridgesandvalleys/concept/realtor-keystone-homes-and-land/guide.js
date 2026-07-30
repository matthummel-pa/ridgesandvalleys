/* =========================================================================
   Keystone Homes & Land — LAND BUYER'S GUIDE page tools
   - Standalone land-loan / mortgage estimate
   - Financing pre-qualification estimate
   - Request info / book-a-call scheduler
   All are illustrative concept demos (no data leaves the browser).
   ========================================================================= */
(function(){
  "use strict";

  var currency = function(n){ return "$" + Math.round(n).toLocaleString("en-US"); };

  /* ============================= LAND-LOAN ESTIMATE ============================= */
  var llForm = document.getElementById("landLoanForm");
  if(llForm){
    function recalcLoan(){
      var price = Number(document.getElementById("llPrice").value) || 0;
      var downPct = Number(document.getElementById("llDown").value) || 0;
      var rate = Number(document.getElementById("llRate").value) || 0;
      var years = Number(document.getElementById("llTerm").value) || 30;

      var loan = price * (1 - downPct/100);
      var monthlyRate = (rate/100)/12;
      var n = years*12;
      var payment = 0;
      if(loan > 0){
        payment = (monthlyRate === 0)
          ? loan/n
          : loan * (monthlyRate * Math.pow(1+monthlyRate, n)) / (Math.pow(1+monthlyRate, n) - 1);
      }
      document.getElementById("llMonthly").textContent = currency(payment) + " /mo";
      document.getElementById("llLoanAmt").textContent = currency(loan);
    }
    ["llPrice","llDown","llRate","llTerm"].forEach(function(id){
      var el = document.getElementById(id);
      el.addEventListener("input", recalcLoan);
      el.addEventListener("change", recalcLoan);
    });
    llForm.addEventListener("submit", function(e){ e.preventDefault(); recalcLoan(); });
    recalcLoan();
  }

  /* ============================= PRE-QUALIFICATION ============================= */
  var pqForm = document.getElementById("preQualForm");
  if(pqForm){
    pqForm.addEventListener("submit", function(e){
      e.preventDefault();
      var income = Number(document.getElementById("pqIncome").value) || 0;
      var debts = Number(document.getElementById("pqDebts").value) || 0;
      var down = Number(document.getElementById("pqDown").value) || 0;
      var rate = Number(document.getElementById("pqRate").value) || 6.75;

      var monthlyIncome = income/12;
      var maxPayment = Math.max(monthlyIncome*0.36 - debts, 0);
      var monthlyRate = (rate/100)/12;
      var n = 30*12;
      var loanAmount = 0;
      if(maxPayment > 0 && monthlyRate > 0){
        loanAmount = maxPayment * (Math.pow(1+monthlyRate,n) - 1) / (monthlyRate * Math.pow(1+monthlyRate,n));
      }
      var maxPrice = loanAmount + down;

      document.getElementById("pqAmount").textContent = currency(maxPrice);
      document.getElementById("pqResult").style.display = "flex";
    });
  }

  /* ============================= SCHEDULER ============================= */
  var schForm = document.getElementById("scheduleForm");
  if(schForm){
    schForm.addEventListener("submit", function(e){
      e.preventDefault();
      document.getElementById("scheduleConfirm").classList.add("show");
      this.reset();
    });
    try{
      var dateInput = document.getElementById("schDate");
      var today = new Date().toISOString().split("T")[0];
      dateInput.setAttribute("min", today);
    }catch(err){}
  }

})();

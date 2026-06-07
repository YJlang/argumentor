import { useState } from "react";
import { useNavigate, useLocation } from "react-router";
import { motion } from "motion/react";
import { ArrowLeft, ArrowRight, AlertTriangle, CheckCircle, Lightbulb, Target, TrendingUp, XCircle, ChevronRight } from "lucide-react";

const tabs = ["논리 구조 분석", "약점 분석", "AI 반박 요약", "개선 전략"];

function LogicStructure() {
  const steps = [
    { label: "주장", content: "해당 정책은 사회에 긍정적 영향을 미친다", color: "from-indigo-500 to-indigo-600" },
    { label: "근거 1", content: "경제적 효율성 증가에 대한 논거", color: "from-purple-500 to-purple-600" },
    { label: "근거 2", content: "사회적 형평성 개선 효과", color: "from-violet-500 to-violet-600" },
    { label: "결론", content: "장기적으로 도입이 바람직하다", color: "from-indigo-500 to-purple-600" },
  ];

  const scores = [
    { label: "논리적 일관성", value: 72 },
    { label: "근거의 타당성", value: 58 },
    { label: "결론의 설득력", value: 65 },
    { label: "반박 대비력", value: 45 },
  ];

  return (
    <div className="space-y-8">
      {/* Flow */}
      <div className="space-y-3">
        <h3 className="text-white/60 text-sm mb-4">주장 흐름도</h3>
        <div className="flex flex-col md:flex-row items-stretch gap-3">
          {steps.map((s, i) => (
            <div key={s.label} className="flex items-center gap-3 flex-1">
              <motion.div
                initial={{ opacity: 0, scale: 0.9 }}
                animate={{ opacity: 1, scale: 1 }}
                transition={{ delay: i * 0.15 }}
                className="flex-1 p-4 rounded-2xl bg-white/[0.03] border border-white/[0.06]"
              >
                <span className={`inline-block text-xs px-2 py-0.5 rounded-full bg-gradient-to-r ${s.color} text-white mb-2`}>{s.label}</span>
                <p className="text-sm text-white/70">{s.content}</p>
              </motion.div>
              {i < steps.length - 1 && <ChevronRight className="w-4 h-4 text-white/20 hidden md:block shrink-0" />}
            </div>
          ))}
        </div>
      </div>

      {/* Scores */}
      <div className="space-y-3">
        <h3 className="text-white/60 text-sm mb-4">논증 점수</h3>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {scores.map((s, i) => (
            <motion.div
              key={s.label}
              initial={{ opacity: 0, x: -10 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ delay: i * 0.1 }}
              className="p-4 rounded-2xl bg-white/[0.03] border border-white/[0.06]"
            >
              <div className="flex justify-between text-sm mb-2">
                <span className="text-white/60">{s.label}</span>
                <span className={s.value >= 70 ? "text-emerald-400" : s.value >= 50 ? "text-amber-400" : "text-rose-400"}>{s.value}점</span>
              </div>
              <div className="h-2 rounded-full bg-white/[0.06] overflow-hidden">
                <motion.div
                  initial={{ width: 0 }}
                  animate={{ width: `${s.value}%` }}
                  transition={{ duration: 0.8, delay: i * 0.1 }}
                  className={`h-full rounded-full ${s.value >= 70 ? "bg-emerald-500" : s.value >= 50 ? "bg-amber-500" : "bg-rose-500"}`}
                />
              </div>
            </motion.div>
          ))}
        </div>
      </div>
    </div>
  );
}

function WeaknessAnalysis() {
  const weaknesses = [
    { type: "논리적 오류", icon: XCircle, color: "text-rose-400", items: ["인과관계 혼동: 상관관계를 인과관계로 잘못 해석함", "성급한 일반화: 제한된 사례로 전체를 판단함"] },
    { type: "부족한 근거", icon: AlertTriangle, color: "text-amber-400", items: ["실증적 데이터 부재: 통계나 연구 결과가 제시되지 않음", "반례 미고려: 주장에 반하는 사례를 검토하지 않음"] },
    { type: "설득력 부족", icon: Target, color: "text-orange-400", items: ["감정에 호소: 논리보다 감정적 언어에 의존함", "대안 부재: 반대 입장의 장점을 인정하지 않음"] },
  ];

  return (
    <div className="space-y-5">
      {weaknesses.map((w, i) => (
        <motion.div
          key={w.type}
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: i * 0.12 }}
          className="p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06]"
        >
          <div className="flex items-center gap-2 mb-4">
            <w.icon className={`w-5 h-5 ${w.color}`} />
            <h3 className="text-white/80">{w.type}</h3>
          </div>
          <div className="space-y-3">
            {w.items.map((item) => (
              <div key={item} className="flex items-start gap-3 pl-2">
                <div className={`w-1.5 h-1.5 rounded-full mt-2 shrink-0 ${w.color.replace("text-", "bg-")}`} />
                <p className="text-sm text-white/60">{item}</p>
              </div>
            ))}
          </div>
        </motion.div>
      ))}
    </div>
  );
}

function RebuttalSummary() {
  const rebuttals = [
    { point: "경제적 효율성 주장에 대한 반박", detail: "단기적 효율성과 장기적 사회 비용을 구분하지 못함. 숨겨진 외부 비용이 존재.", severity: "high" },
    { point: "사회적 형평성 논거 반박", detail: "특정 계층에 대한 혜택만 강조하고, 다른 계층에 미치는 부정적 영향을 무시.", severity: "medium" },
    { point: "실증적 근거 부재 지적", detail: "주장을 뒷받침하는 구체적인 연구 데이터나 사례가 제시되지 않음.", severity: "high" },
    { point: "대안적 해결책 부재", detail: "현재 제안 외에 더 효율적이고 형평성 높은 대안이 존재할 수 있음.", severity: "low" },
  ];

  return (
    <div className="space-y-4">
      {rebuttals.map((r, i) => (
        <motion.div
          key={r.point}
          initial={{ opacity: 0, x: -10 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ delay: i * 0.1 }}
          className="flex gap-4 p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06] hover:border-indigo-500/20 transition-colors"
        >
          <div className={`w-1 rounded-full shrink-0 ${r.severity === "high" ? "bg-rose-500" : r.severity === "medium" ? "bg-amber-500" : "bg-emerald-500"}`} />
          <div>
            <h4 className="text-white/80 text-sm mb-1">{r.point}</h4>
            <p className="text-sm text-white/45">{r.detail}</p>
          </div>
        </motion.div>
      ))}
    </div>
  );
}

function ImprovementStrategy() {
  const strategies = [
    { icon: CheckCircle, title: "실증적 근거 보강", items: ["관련 통계 데이터 인용하기", "학술 연구 결과 참조하기", "실제 사례 제시하기"], color: "text-emerald-400" },
    { icon: TrendingUp, title: "논리 구조 강화", items: ["전제 → 근거 → 결론 흐름 정리하기", "반례에 대한 사전 대비 논리 구성하기", "조건부 주장으로 전환하기"], color: "text-indigo-400" },
    { icon: Lightbulb, title: "설득력 향상", items: ["반대 입장 일부 인정 후 반박하기", "구체적인 수치와 비교 활용하기", "청중의 관점에서 재구성하기"], color: "text-purple-400" },
  ];

  return (
    <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
      {strategies.map((s, i) => (
        <motion.div
          key={s.title}
          initial={{ opacity: 0, y: 15 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: i * 0.12 }}
          className="p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06]"
        >
          <s.icon className={`w-6 h-6 ${s.color} mb-3`} />
          <h3 className="text-white/80 mb-3">{s.title}</h3>
          <ul className="space-y-2.5">
            {s.items.map((item) => (
              <li key={item} className="flex items-start gap-2 text-sm text-white/50">
                <ArrowRight className="w-3.5 h-3.5 mt-0.5 shrink-0 text-white/20" />
                {item}
              </li>
            ))}
          </ul>
        </motion.div>
      ))}
    </div>
  );
}

const tabComponents = [LogicStructure, WeaknessAnalysis, RebuttalSummary, ImprovementStrategy];

export default function AnalysisDashboard() {
  const navigate = useNavigate();
  const location = useLocation();
  const { topic } = (location.state as any) || { topic: "인공지능이 인간의 일자리를 대체해야 하는가" };
  const [activeTab, setActiveTab] = useState(0);

  const ActiveComponent = tabComponents[activeTab];

  return (
    <div className="min-h-screen flex flex-col">
      {/* Header */}
      <div className="px-4 md:px-8 py-4 border-b border-white/[0.06] bg-white/[0.02]">
        <div className="flex items-center gap-3 mb-4">
          <button onClick={() => navigate(-1)} className="p-2 rounded-xl hover:bg-white/5 transition-colors cursor-pointer">
            <ArrowLeft className="w-4 h-4 text-white/50" />
          </button>
          <div>
            <h2 className="text-sm text-white/80">분석 대시보드</h2>
            <p className="text-xs text-white/30 truncate max-w-[400px]">{topic}</p>
          </div>
        </div>

        {/* Tabs */}
        <div className="flex gap-1 overflow-x-auto pb-1 scrollbar-hide">
          {tabs.map((tab, i) => (
            <button
              key={tab}
              onClick={() => setActiveTab(i)}
              className={`px-4 py-2 rounded-xl text-sm whitespace-nowrap transition-all cursor-pointer ${
                activeTab === i
                  ? "bg-indigo-600/20 text-indigo-300 border border-indigo-500/30"
                  : "text-white/40 hover:text-white/60 hover:bg-white/[0.04] border border-transparent"
              }`}
            >
              {tab}
            </button>
          ))}
        </div>
      </div>

      {/* Content */}
      <div className="flex-1 px-4 md:px-8 py-6 max-w-5xl w-full mx-auto">
        <motion.div key={activeTab} initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.3 }}>
          <ActiveComponent />
        </motion.div>
      </div>

      {/* Glow */}
      <div className="fixed bottom-0 left-1/2 -translate-x-1/2 w-[500px] h-[300px] bg-indigo-600/5 rounded-full blur-[100px] pointer-events-none" />
    </div>
  );
}

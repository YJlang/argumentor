import { useNavigate } from "react-router";
import { motion } from "motion/react";
import { Brain, Swords, BarChart3, Sparkles, ArrowRight } from "lucide-react";

const features = [
  { icon: Brain, title: "AI 반대 논리 생성", desc: "AI가 자동으로 반대 입장의 논리를 구성합니다" },
  { icon: Swords, title: "실시간 반박 시뮬레이션", desc: "실제 토론처럼 주고받는 반박을 경험하세요" },
  { icon: BarChart3, title: "논리 구조 분석", desc: "논증의 강점과 약점을 시각적으로 분석합니다" },
];

export default function LandingPage() {
  const navigate = useNavigate();

  return (
    <div className="min-h-screen flex flex-col">
      {/* Nav */}
      <nav className="flex items-center justify-between px-6 py-4 md:px-12">
        <div className="flex items-center gap-2">
          <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
            <Sparkles className="w-4 h-4 text-white" />
          </div>
          <span className="text-white/90">AI 논리 토론</span>
        </div>
      </nav>

      {/* Hero */}
      <div className="flex-1 flex flex-col items-center justify-center px-6 text-center">
        <motion.div
          initial={{ opacity: 0, y: 30 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.7 }}
          className="max-w-3xl"
        >
          <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-indigo-500/30 bg-indigo-500/10 text-indigo-300 mb-8 text-sm">
            <Sparkles className="w-3.5 h-3.5" />
            AI 기반 논리 훈련 도구
          </div>
          <h1 className="text-4xl md:text-6xl bg-gradient-to-r from-white via-indigo-200 to-purple-300 bg-clip-text text-transparent mb-6 tracking-tight" style={{ lineHeight: 1.2 }}>
            AI 논리 토론 시뮬레이터
          </h1>
          <p className="text-lg text-white/50 mb-10 max-w-xl mx-auto">
            AI와 함께 논리를 검증하고 사고력을 확장하세요.
            <br />어떤 주제든 깊이 있는 토론을 경험할 수 있습니다.
          </p>
          <motion.button
            whileHover={{ scale: 1.03 }}
            whileTap={{ scale: 0.97 }}
            onClick={() => navigate("/setup")}
            className="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-shadow cursor-pointer"
          >
            토론 시작하기
            <ArrowRight className="w-4 h-4" />
          </motion.button>
        </motion.div>

        {/* Features */}
        <motion.div
          initial={{ opacity: 0, y: 40 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.7, delay: 0.3 }}
          className="grid grid-cols-1 md:grid-cols-3 gap-5 mt-20 max-w-4xl w-full"
        >
          {features.map((f, i) => (
            <motion.div
              key={f.title}
              whileHover={{ y: -4 }}
              transition={{ type: "spring", stiffness: 300 }}
              className="p-6 rounded-2xl bg-white/[0.03] border border-white/[0.06] backdrop-blur-sm hover:border-indigo-500/30 transition-colors"
            >
              <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 flex items-center justify-center mb-4">
                <f.icon className="w-5 h-5 text-indigo-400" />
              </div>
              <h3 className="text-white/90 mb-2">{f.title}</h3>
              <p className="text-sm text-white/40">{f.desc}</p>
            </motion.div>
          ))}
        </motion.div>
      </div>

      {/* Glow effect */}
      <div className="fixed top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-600/10 rounded-full blur-[120px] pointer-events-none" />
    </div>
  );
}

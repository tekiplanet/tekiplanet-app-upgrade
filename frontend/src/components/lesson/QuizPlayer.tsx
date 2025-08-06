import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Progress } from "@/components/ui/progress";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { 
  CheckCircle, 
  XCircle, 
  HelpCircle, 
  Clock,
  Trophy,
  AlertCircle,
  Loader2
} from "lucide-react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { lessonService } from '@/services/lessonService';
import { toast } from "sonner";

interface QuizQuestion {
  id: string;
  question: string;
  question_type: 'multiple_choice' | 'true_false' | 'short_answer';
  points: number;
  order: number;
  answers: QuizAnswer[];
}

interface QuizAnswer {
  id: string;
  answer_text: string;
  is_correct: boolean;
  order: number;
}

interface QuizAttempt {
  id: string;
  user_id: string;
  lesson_id: string;
  score: number;
  total_points: number;
  percentage: number;
  passed: boolean;
  started_at: string;
  completed_at?: string;
  learn_rewards_earned?: number;
  total_learn_rewards?: number;
}

interface QuizResponse {
  id: string;
  attempt_id: string;
  question_id: string;
  user_answer: string;
  is_correct: boolean;
  points_earned: number;
  question: QuizQuestion;
}

interface QuizPlayerProps {
  lessonId: string;
}

export default function QuizPlayer({ lessonId }: QuizPlayerProps) {
  const [currentQuestionIndex, setCurrentQuestionIndex] = useState(0);
  const [answers, setAnswers] = useState<Record<string, string>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [showResults, setShowResults] = useState(false);
  const [attempt, setAttempt] = useState<QuizAttempt | null>(null);
  const [results, setResults] = useState<any>(null);
  const [showDetailedResults, setShowDetailedResults] = useState(false);
  const [isRetaking, setIsRetaking] = useState(false);
  const [showRewardDialog, setShowRewardDialog] = useState(false);
  const [rewardData, setRewardData] = useState<{ earned: number; total: number } | null>(null);
  
  const queryClient = useQueryClient();

  // Get quiz questions
  const { 
    data: questionsData, 
    isLoading: isLoadingQuestions,
    error: questionsError 
  } = useQuery({
    queryKey: ['quiz-questions', lessonId],
    queryFn: () => lessonService.getQuizQuestions(lessonId),
    enabled: !!lessonId
  });

  // Start quiz attempt mutation
  const startAttemptMutation = useMutation({
    mutationFn: () => lessonService.startQuizAttempt(lessonId),
    onSuccess: (data) => {
      if (data.success) {
        setAttempt(data.attempt);
      }
    },
    onError: (error) => {
      toast.error('Failed to start quiz');
    }
  });

  // Submit quiz answers mutation
  const submitAnswersMutation = useMutation({
    mutationFn: (data: { attempt_id: string; answers: Array<{ question_id: string; user_answer: string }> }) =>
      lessonService.submitQuizAnswers(lessonId, data),
    onSuccess: (data) => {
      if (data.success) {
        setResults(data);
        setShowResults(true);
        setIsSubmitting(false);
        
        if (data.passed) {
          if (data.learn_rewards_earned > 0) {
            setRewardData({
              earned: data.learn_rewards_earned,
              total: data.total_learn_rewards
            });
            setShowRewardDialog(true);
            // Invalidate lesson progress to update it
            queryClient.invalidateQueries({ queryKey: ['lesson-progress'] });
          } else {
            toast.success('Congratulations! You passed the quiz!');
            // Invalidate lesson progress to update it
            queryClient.invalidateQueries({ queryKey: ['lesson-progress'] });
          }
        } else {
          toast.error('You did not pass the quiz. You can retake it to improve your score.');
        }
      }
    },
    onError: (error) => {
      setIsSubmitting(false);
      toast.error('Failed to submit quiz');
    }
  });

  // Get existing quiz results (to check if user has already passed)
  const { 
    data: existingResultsData, 
    isLoading: isLoadingExistingResults 
  } = useQuery({
    queryKey: ['existing-quiz-results', lessonId],
    queryFn: () => lessonService.getQuizResults(lessonId),
    enabled: !!lessonId && !showResults,
    retry: false
  });

  // Get quiz results for current attempt
  const { 
    data: resultsData, 
    isLoading: isLoadingResults 
  } = useQuery({
    queryKey: ['quiz-results', lessonId],
    queryFn: () => lessonService.getQuizResults(lessonId),
    enabled: !!lessonId && showResults
  });

  const questions = questionsData?.questions || [];
  const currentQuestion = questions[currentQuestionIndex];

  // Check for existing quiz results when component loads
  useEffect(() => {
    if (existingResultsData?.success && existingResultsData.attempt?.passed) {
      // User has already passed the quiz, show results
      setResults(existingResultsData.attempt);
      setShowResults(true);
    }
  }, [existingResultsData]);

  // Start quiz when questions are loaded (only if no existing passed results)
  useEffect(() => {
    if (questions.length > 0 && !attempt && !existingResultsData?.attempt?.passed) {
      startAttemptMutation.mutate();
    }
  }, [questions, attempt, existingResultsData]);

  const handleAnswerChange = (questionId: string, answer: string) => {
    setAnswers(prev => ({
      ...prev,
      [questionId]: answer
    }));
  };

  const handleNextQuestion = () => {
    if (currentQuestionIndex < questions.length - 1) {
      setCurrentQuestionIndex(prev => prev + 1);
    }
  };

  const handlePreviousQuestion = () => {
    if (currentQuestionIndex > 0) {
      setCurrentQuestionIndex(prev => prev - 1);
    }
  };

  const handleSubmitQuiz = () => {
    if (!attempt) return;

    const answersArray = Object.entries(answers).map(([questionId, userAnswer]) => ({
      question_id: questionId,
      user_answer: userAnswer
    }));

    setIsSubmitting(true);
    submitAnswersMutation.mutate({
      attempt_id: attempt.id,
      answers: answersArray
    });
  };

  const handleRetakeQuiz = () => {
    setIsRetaking(true);
    setShowResults(false);
    setShowDetailedResults(false);
    setCurrentQuestionIndex(0);
    setAnswers({});
    setAttempt(null);
    setResults(null);
    // Reset the query to start a new attempt
    queryClient.invalidateQueries({ queryKey: ['quiz-questions', lessonId] });
  };

  const renderQuestion = (question: QuizQuestion) => {
    const currentAnswer = answers[question.id] || '';

    switch (question.question_type) {
      case 'multiple_choice':
        return (
          <RadioGroup
            value={currentAnswer}
            onValueChange={(value) => handleAnswerChange(question.id, value)}
          >
            <div className="space-y-3">
              {question.answers.map((answer) => (
                <div key={answer.id} className="flex items-start space-x-3 p-3 rounded-lg border hover:bg-muted/50 transition-colors">
                  <RadioGroupItem value={answer.answer_text} id={answer.id} className="mt-1" />
                  <Label htmlFor={answer.id} className="flex-1 cursor-pointer text-sm leading-relaxed">
                    {answer.answer_text}
                  </Label>
                </div>
              ))}
            </div>
          </RadioGroup>
        );

      case 'true_false':
        return (
          <RadioGroup
            value={currentAnswer}
            onValueChange={(value) => handleAnswerChange(question.id, value)}
          >
            <div className="space-y-3">
              {question.answers.map((answer) => (
                <div key={answer.id} className="flex items-start space-x-3 p-3 rounded-lg border hover:bg-muted/50 transition-colors">
                  <RadioGroupItem value={answer.answer_text} id={answer.id} className="mt-1" />
                  <Label htmlFor={answer.id} className="flex-1 cursor-pointer text-sm leading-relaxed">
                    {answer.answer_text}
                  </Label>
                </div>
              ))}
            </div>
          </RadioGroup>
        );

      case 'short_answer':
        return (
          <div className="space-y-2">
            <Textarea
              placeholder="Enter your answer..."
              value={currentAnswer}
              onChange={(e) => handleAnswerChange(question.id, e.target.value)}
              className="min-h-[120px] resize-none"
            />
          </div>
        );

      default:
        return <div>Question type not supported</div>;
    }
  };

  const renderResults = () => {
    if (!results) return null;

    return (
      <div className="space-y-6">
        <div className="text-center">
          <div className="mb-4">
            {results.passed ? (
              <Trophy className="h-16 w-16 text-green-500 mx-auto mb-4" />
            ) : (
              <AlertCircle className="h-16 w-16 text-orange-500 mx-auto mb-4" />
            )}
          </div>
          
          <h2 className="text-2xl font-bold mb-2">
            {results.passed ? 'Congratulations!' : 'Quiz Completed'}
          </h2>
          
          <p className="text-muted-foreground mb-4">
            {results.passed 
              ? 'You passed the quiz!' 
              : 'You did not achieve the required score to pass this quiz.'
            }
          </p>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
            <Card>
              <CardContent className="p-4 text-center">
                <div className="text-xl sm:text-2xl font-bold text-blue-600">
                  {results.score}/{results.total_points}
                </div>
                <div className="text-xs sm:text-sm text-muted-foreground">Score</div>
              </CardContent>
            </Card>
            
            <Card>
              <CardContent className="p-4 text-center">
                <div className="text-xl sm:text-2xl font-bold text-green-600">
                  {Math.round(results.percentage)}%
                </div>
                <div className="text-xs sm:text-sm text-muted-foreground">Percentage</div>
              </CardContent>
            </Card>
            
            <Card>
              <CardContent className="p-4 text-center">
                <div className="text-xl sm:text-2xl font-bold text-purple-600">
                  {results.passed ? 'Passed' : 'Failed'}
                </div>
                <div className="text-xs sm:text-sm text-muted-foreground">Status</div>
              </CardContent>
            </Card>
          </div>

          <Progress value={results.percentage} className="h-3 mb-4" />
        </div>

        {/* Show detailed results only if passed or user explicitly requests it */}
        {results.passed && resultsData?.responses && (
          <div className="space-y-4">
            <h3 className="text-lg font-semibold">Question Review</h3>
            {resultsData.responses.map((response: QuizResponse, index: number) => (
              <Card key={response.id}>
                <CardContent className="p-4">
                  <div className="flex items-start gap-3">
                    <div className="flex-shrink-0 mt-1">
                      {response.is_correct ? (
                        <CheckCircle className="h-5 w-5 text-green-500" />
                      ) : (
                        <XCircle className="h-5 w-5 text-red-500" />
                      )}
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="font-medium mb-2 text-sm leading-relaxed">
                        {index + 1}. {response.question.question}
                      </p>
                      <div className="space-y-2 text-xs sm:text-sm">
                        <div className="break-words">
                          <span className="font-medium">Your answer:</span> {response.user_answer}
                        </div>
                        <div>
                          <span className="font-medium">Points:</span> {response.points_earned}/{response.question.points}
                        </div>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}

        {/* Action buttons */}
        <div className="flex flex-col sm:flex-row gap-3 pt-6 border-t">
          {!results.passed && (
            <Button 
              onClick={handleRetakeQuiz}
              className="w-full sm:w-auto"
            >
              Retake Quiz
            </Button>
          )}
          
          {!results.passed && !showDetailedResults && (
            <Button 
              variant="outline"
              onClick={() => setShowDetailedResults(true)}
              className="w-full sm:w-auto"
            >
              View Detailed Results
            </Button>
          )}
          
          {!results.passed && showDetailedResults && resultsData?.responses && (
            <div className="w-full space-y-4">
              <h3 className="text-lg font-semibold">Question Review</h3>
              {resultsData.responses.map((response: QuizResponse, index: number) => (
                <Card key={response.id}>
                  <CardContent className="p-4">
                    <div className="flex items-start gap-3">
                      <div className="flex-shrink-0 mt-1">
                        {response.is_correct ? (
                          <CheckCircle className="h-5 w-5 text-green-500" />
                        ) : (
                          <XCircle className="h-5 w-5 text-red-500" />
                        )}
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="font-medium mb-2 text-sm leading-relaxed">
                          {index + 1}. {response.question.question}
                        </p>
                        <div className="space-y-2 text-xs sm:text-sm">
                          <div className="break-words">
                            <span className="font-medium">Your answer:</span> {response.user_answer}
                          </div>
                          <div>
                            <span className="font-medium">Points:</span> {response.points_earned}/{response.question.points}
                          </div>
                        </div>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          )}
        </div>
      </div>
    );
  };

  if (isLoadingQuestions || isLoadingExistingResults) {
    return (
      <div className="flex items-center justify-center py-12">
        <Loader2 className="h-8 w-8 animate-spin" />
        <span className="ml-2">Loading quiz...</span>
      </div>
    );
  }

  if (questionsError) {
    return (
      <div className="text-center py-12">
        <AlertCircle className="h-16 w-16 text-red-500 mx-auto mb-4" />
        <h3 className="text-lg font-semibold mb-2">Error Loading Quiz</h3>
        <p className="text-muted-foreground">
          Failed to load quiz questions. Please try again.
        </p>
      </div>
    );
  }

  if (questions.length === 0) {
    return (
      <div className="text-center py-12">
        <HelpCircle className="h-16 w-16 text-muted-foreground mx-auto mb-4" />
        <h3 className="text-lg font-semibold mb-2">No Quiz Available</h3>
        <p className="text-muted-foreground">
          This lesson doesn't have any quiz questions yet.
        </p>
      </div>
    );
  }

  if (showResults) {
    return (
      <>
        <Card>
          <CardHeader>
            <CardTitle>Quiz Results</CardTitle>
          </CardHeader>
          <CardContent>
            {renderResults()}
          </CardContent>
        </Card>
        
        {/* Reward Dialog */}
        {showRewardDialog && rewardData && (
          <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div className="bg-white rounded-lg p-8 max-w-md w-full mx-4 shadow-xl">
              <div className="text-center">
                {/* Success Icon */}
                <div className="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                  <svg className="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                
                {/* Title */}
                <h3 className="text-xl font-bold text-gray-900 mb-2">
                  Congratulations! 🎉
                </h3>
                
                {/* Message */}
                <p className="text-gray-600 mb-6">
                  You passed the quiz and earned rewards!
                </p>
                
                {/* Rewards Display */}
                <div className="bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg p-4 mb-6">
                  <div className="text-center">
                    <div className="text-2xl font-bold text-white mb-1">
                      +{rewardData.earned} Learn Rewards
                    </div>
                    <div className="text-yellow-100 text-sm">
                      Total: {rewardData.total} Learn Rewards
                    </div>
                  </div>
                </div>
                
                {/* Action Button */}
                <Button 
                  onClick={() => setShowRewardDialog(false)}
                  className="w-full bg-green-600 hover:bg-green-700"
                >
                  Continue
                </Button>
              </div>
            </div>
          </div>
        )}
      </>
    );
  }

  return (
    <Card>
      <CardHeader className="space-y-4">
        <div className="flex flex-col gap-3">
          {/* Mobile-optimized question counter and points */}
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div className="flex items-center gap-2">
              <Badge variant="secondary" className="text-sm">
                {currentQuestionIndex + 1} of {questions.length}
              </Badge>
              <Badge variant="outline" className="text-sm">
                {currentQuestion?.points || 1} pt{currentQuestion?.points !== 1 ? 's' : ''}
              </Badge>
            </div>
          </div>
          
          {/* Progress Bar */}
          <div className="space-y-2">
            <div className="flex justify-between text-sm">
              <span>Progress</span>
              <span>{Math.round(((currentQuestionIndex + 1) / questions.length) * 100)}%</span>
            </div>
            <Progress value={((currentQuestionIndex + 1) / questions.length) * 100} className="h-2" />
          </div>
        </div>
      </CardHeader>
      
      <CardContent className="space-y-6">
        {currentQuestion && (
          <>
            <div className="space-y-4">
              <h3 className="text-lg font-medium leading-relaxed">
                {currentQuestion.question}
              </h3>
              
              <div className="space-y-4">
                {renderQuestion(currentQuestion)}
              </div>
            </div>

            <div className="flex flex-col sm:flex-row justify-between gap-3 pt-6 border-t">
              <Button
                variant="outline"
                onClick={handlePreviousQuestion}
                disabled={currentQuestionIndex === 0}
                className="w-full sm:w-auto"
              >
                Previous
              </Button>

              <div className="flex gap-2 w-full sm:w-auto">
                {currentQuestionIndex < questions.length - 1 ? (
                  <Button onClick={handleNextQuestion} className="flex-1 sm:flex-none">
                    Next Question
                  </Button>
                ) : (
                  <Button 
                    onClick={handleSubmitQuiz}
                    disabled={isSubmitting || Object.keys(answers).length < questions.length}
                    className="flex-1 sm:flex-none"
                  >
                    {isSubmitting ? (
                      <>
                        <Loader2 className="h-4 w-4 animate-spin mr-2" />
                        Submitting...
                      </>
                    ) : (
                      'Submit Quiz'
                    )}
                  </Button>
                )}
              </div>
            </div>
          </>
        )}
      </CardContent>
    </Card>
  );
} 
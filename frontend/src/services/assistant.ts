import type { ChatResponse } from '../types';
import { api } from './api';

export async function askAssistant(question: string): Promise<ChatResponse> {
  const { data } = await api.post<{ data: ChatResponse }>('/v1/assistant/query', {
    question,
  });

  return data.data;
}

export async function getAssistantSuggestions(): Promise<string[]> {
  const { data } = await api.get<{ data: string[] }>('/v1/assistant/suggestions');

  return data.data;
}

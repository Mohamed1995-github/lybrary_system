'use client'

import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react'

interface User {
  id: string
  email: string
  name: string
  role: 'CLIENT' | 'AGENCY'
  agency?: {
    id: string
    name: string
    description?: string
    address: string
    phone: string
    email: string
    website?: string
  }
}

interface AuthContextType {
  user: User | null
  token: string | null
  login: (email: string, password: string) => Promise<void>
  register: (userData: any) => Promise<void>
  logout: () => void
  loading: boolean
}

const AuthContext = createContext<AuthContextType | undefined>(undefined)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [token, setToken] = useState<string | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // Charger le token depuis le localStorage au démarrage
    const savedToken = localStorage.getItem('token')
    if (savedToken) {
      setToken(savedToken)
      // TODO: Vérifier la validité du token et charger les données utilisateur
    }
    setLoading(false)
  }, [])

  const login = async (email: string, password: string) => {
    try {
      const response = await fetch('/api/auth/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email, password }),
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.error || 'Erreur de connexion')
      }

      setUser(data.user)
      setToken(data.token)
      localStorage.setItem('token', data.token)
    } catch (error) {
      console.error('Erreur de connexion:', error)
      throw error
    }
  }

  const register = async (userData: any) => {
    try {
      const response = await fetch('/api/auth/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(userData),
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.error || 'Erreur d\'inscription')
      }

      setUser(data.user)
      setToken(data.token)
      localStorage.setItem('token', data.token)
    } catch (error) {
      console.error('Erreur d\'inscription:', error)
      throw error
    }
  }

  const logout = () => {
    setUser(null)
    setToken(null)
    localStorage.removeItem('token')
  }

  return (
    <AuthContext.Provider value={{ user, token, login, register, logout, loading }}>
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  const context = useContext(AuthContext)
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider')
  }
  return context
}
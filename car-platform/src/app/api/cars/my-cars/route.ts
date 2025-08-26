import { NextRequest, NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { getTokenFromRequest, verifyToken } from '@/lib/auth'

export async function GET(request: NextRequest) {
  try {
    const token = getTokenFromRequest(request)
    if (!token) {
      return NextResponse.json(
        { error: 'Token d\'authentification manquant' },
        { status: 401 }
      )
    }

    const payload = verifyToken(token)
    if (!payload || payload.role !== 'AGENCY') {
      return NextResponse.json(
        { error: 'Accès non autorisé' },
        { status: 403 }
      )
    }

    // Vérifier que l'utilisateur a une agence
    const user = await prisma.user.findUnique({
      where: { id: payload.userId },
      include: { agency: true }
    })

    if (!user?.agency) {
      return NextResponse.json(
        { error: 'Aucune agence associée à cet utilisateur' },
        { status: 400 }
      )
    }

    const cars = await prisma.car.findMany({
      where: {
        agencyId: user.agency.id
      },
      orderBy: {
        createdAt: 'desc'
      }
    })

    return NextResponse.json(cars)

  } catch (error) {
    console.error('Erreur lors de la récupération des voitures:', error)
    return NextResponse.json(
      { error: 'Erreur interne du serveur' },
      { status: 500 }
    )
  }
}